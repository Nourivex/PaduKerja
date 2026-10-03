<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobVacancy;
use App\Models\PipelineTimeline;
use App\Services\MatchmakingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class ApplicationController
 *
 * Mengelola fungsionalitas Paket B: Pengajuan Lamaran & Mesin Skoring Otomatis.
 * Bagian dari Layanan Microservice: Application & Matchmaking Service (Penanggung Jawab: NABE'ELA AYU NING TYAZ ZAHRA).
 *
 * Pokok Bahasan Pembelajaran Mahasiswa:
 * 1. Method POST: Mengirim data lamaran pekerjaan baru dari pelamar.
 * 2. Integrasi Layanan (Service): Menghubungkan controller dengan MatchmakingService untuk menghitung skor kecocokan.
 * 3. Transaksi Database (DB::transaction): Menjamin bahwa pembuatan data lamaran dan linimasa pipeline
 *    harus berhasil secara bersamaan (Atomic), jika salah satu gagal maka seluruh perubahan dibatalkan (Rollback).
 * 4. Penanganan Konflik Data (HTTP 409 Conflict): Mencegah pelamar mengirimkan lamaran ganda pada lowongan yang sama.
 * 5. Method DELETE: Membatalkan pengajuan berkas lamaran dengan verifikasi kepemilikan data (Ownership Authorization).
 */
class ApplicationController extends Controller
{
    use ApiResponse;

    public function __construct(protected MatchmakingService $matchmakingService)
    {
    }

    /**
     * Menampilkan daftar lamaran kerja yang diajukan oleh pengguna saat ini.
     *
     * Method  : GET
     * Endpoint: /api/applications
     */
    public function index(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('auth_user') ?? $request->user();

        $query = Application::with(['jobVacancy:id,title,company,location', 'pipelineTimeline']);

        // Jika pelamar, hanya tampilkan lamaran milik dirinya sendiri
        if ($currentUser->role === 'applicant') {
            $query->where('user_id', $currentUser->id);
        } elseif ($currentUser->role === 'recruiter') {
            // Jika recruiter, tampilkan lamaran yang masuk pada lowongan miliknya
            $recruiterJobIds = JobVacancy::where('posted_by', $currentUser->id)->pluck('id');
            $query->whereIn('job_vacancy_id', $recruiterJobIds);
        }

        $applications = $query->latest()->get();

        return $this->successResponse($applications, 'Daftar berkas lamaran berhasil diambil.');
    }

    /**
     * Menampilkan rincian detail satu berkas lamaran.
     *
     * Method  : GET
     * Endpoint: /api/applications/{id}
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('auth_user') ?? $request->user();

        $application = Application::with(['jobVacancy', 'applicant:id,name,email,skills', 'pipelineTimeline'])->find($id);

        if (! $application) {
            return $this->notFoundResponse('Data berkas lamaran tidak ditemukan.');
        }

        // Pengecekan otorisasi: Pelamar tidak boleh melihat isi lamaran milik orang lain
        if ($currentUser->role === 'applicant' && $application->user_id !== $currentUser->id) {
            return $this->forbiddenResponse('Akses dilarang. Anda tidak memiliki izin untuk melihat lamaran kandidat lain.');
        }

        return $this->successResponse($application, 'Detail berkas lamaran berhasil dimuat.');
    }

    /**
     * Paket B: Mengirim lamaran + otomatis hitung skor kecocokan profil keahlian.
     *
     * Method  : POST
     * Endpoint: /api/applications
     * Headers : Authorization: Bearer <token_pelamar>, Content-Type: application/json
     *
     * @param  Request       $request  ID lowongan, surat pengantar, dan keahlian pelamar
     * @return JsonResponse           JSON envelope HTTP 201 Created beserta rincian skor kecocokan
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Ambil data pengguna pelamar yang terotentikasi
        $currentUser = $request->attributes->get('auth_user') ?? $request->user();

        if (! $currentUser) {
            return $this->unauthorizedResponse();
        }

        // 2. Validasi input: pastikan ID lowongan valid dan terdaftar di database
        $validator = Validator::make($request->all(), [
            'job_id' => 'nullable|integer|exists:job_vacancies,id',
            'job_vacancy_id' => 'nullable|integer|exists:job_vacancies,id',
            'cover_letter' => 'nullable|string',
            'candidate_skills' => 'nullable|array',
        ], [
            'job_vacancy_id.exists' => 'Lowongan pekerjaan yang dituju tidak ditemukan.',
            'job_id.exists' => 'Lowongan pekerjaan yang dituju tidak ditemukan.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $jobVacancyId = $request->input('job_vacancy_id') ?? $request->input('job_id');

        if (! $jobVacancyId) {
            return $this->validationErrorResponse(['job_id' => ['Parameter job_id atau job_vacancy_id wajib diisi.']]);
        }

        // 3. Pastikan lowongan berstatus buka ('open')
        $job = JobVacancy::find($jobVacancyId);

        if (! $job || $job->status !== 'open') {
            return $this->errorResponse('Lowongan ini telah ditutup dan tidak lagi menerima berkas lamaran baru.', 400);
        }

        // 4. Cek apakah pelamar sudah pernah mendaftar pada lowongan ini (Cegah Duplikasi)
        $existing = Application::where('user_id', $currentUser->id)
            ->where('job_vacancy_id', $jobVacancyId)
            ->where('status', '!=', 'canceled')
            ->first();

        if ($existing) {
            // Mengembalikan HTTP 409 Conflict jika terdapat duplikasi data
            return $this->errorResponse('Anda telah mengajukan lamaran untuk lowongan ini sebelumnya.', 409, [
                'application_id' => $existing->id,
                'status' => $existing->status,
                'applied_at' => $existing->created_at->toIso8601String(),
            ]);
        }

        // 5. Tentukan daftar keahlian pelamar (ambil dari request body atau fallback ke profil database pengguna)
        $candidateSkills = $request->input('candidate_skills') ?? $currentUser->skills ?? [];

        // 6. Jalankan Mesin Skoring Pemadanan Keahlian (Weighted Jaccard Similarity Engine)
        $matchResult = $this->matchmakingService->calculate(
            $candidateSkills,
            $job->requirements ?? []
        );

        // 7. Simpan lamaran dan buat linimasa pipeline secara atomic di dalam Database Transaction
        return DB::transaction(function () use ($currentUser, $job, $request, $matchResult, $candidateSkills) {
            // A. Simpan data lamaran kerja
            $application = Application::create([
                'user_id' => $currentUser->id,
                'job_vacancy_id' => $job->id,
                'cover_letter' => $request->cover_letter,
                'match_score' => $matchResult['score'],
                'match_category' => $matchResult['category'],
                'matched_skills' => $matchResult['matched_skills'],
                'missing_skills' => $matchResult['missing_skills'],
                'status' => 'submitted',
            ]);

            // B. Tentukan status awal tahap screening:
            //    Jika skor >= 80%, otomatis 'passed', jika di bawah 80% maka 'in_progress' untuk diulas manual
            $initialStageStatus = $matchResult['score'] >= 80.0 ? 'passed' : 'in_progress';
            $screeningCompletedAt = $matchResult['score'] >= 80.0 ? now()->toIso8601String() : null;

            $initialStages = [
                [
                    'stage' => 'screening',
                    'status' => $initialStageStatus,
                    'entered_at' => now()->toIso8601String(),
                    'completed_at' => $screeningCompletedAt,
                    'notes' => "Hasil Mesin Pemadanan Otomatis: Skor {$matchResult['score']}%. Kategori: {$matchResult['category']}.",
                ],
            ];

            // C. Inisialisasi riwayat linimasa (Recruitment Pipeline Timeline)
            $pipeline = PipelineTimeline::create([
                'application_id' => $application->id,
                'current_stage' => 'screening',
                'stages' => $initialStages,
            ]);

            // D. Kembalikan response HTTP 201 Created beserta data kalkulasi skor
            return $this->createdResponse([
                'application_id' => $application->id,
                'job' => [
                    'id' => $job->id,
                    'title' => $job->title,
                    'company' => $job->company,
                ],
                'applicant' => [
                    'id' => $currentUser->id,
                    'name' => $currentUser->name,
                    'skills_used' => $candidateSkills,
                ],
                'matchmaking_result' => [
                    'match_score' => $matchResult['score'],
                    'match_category' => $matchResult['category'],
                    'matched_skills' => $matchResult['matched_skills'],
                    'missing_skills' => $matchResult['missing_skills'],
                    'calculation_details' => $matchResult['calculation_details'],
                ],
                'pipeline_status' => [
                    'current_stage' => $pipeline->current_stage,
                    'stage_status' => $initialStageStatus,
                ],
                'applied_at' => $application->created_at->toIso8601String(),
            ], 'Berkas lamaran berhasil dikirim. Skor pemadanan kompetensi dihitung secara otomatis.');
        });
    }

    /**
     * Paket B: Membatalkan pengajuan berkas lamaran kerja.
     *
     * Method  : DELETE
     * Endpoint: /api/applications/{id}
     * Headers : Authorization: Bearer <token_pelamar>
     *
     * @param  int           $id       ID berkas lamaran yang ingin dibatalkan
     * @param  Request       $request  Objek HTTP Request
     * @return JsonResponse           JSON envelope konfirmasi pembatalan
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('auth_user') ?? $request->user();

        $application = Application::with('jobVacancy:id,title,company')->find($id);

        if (! $application) {
            return $this->notFoundResponse("Data lamaran dengan nomor ID {$id} tidak ditemukan.");
        }

        // Verifikasi kepemilikan: Hanya pelamar yang bersangkutan (atau admin) yang boleh membatalkan
        if ($application->user_id !== $currentUser->id && $currentUser->role !== 'admin') {
            return $this->forbiddenResponse('Akses dilarang. Anda tidak berhak membatalkan berkas lamaran milik pelamar lain.');
        }

        // Jika lamaran telah mencapai tahap final offering atau sudah diterima, jangan izinkan pembatalan langsung
        if (in_array($application->status, ['offering', 'accepted'])) {
            return $this->errorResponse('Lamaran yang telah memasuki tahap Offering atau Penerimaan tidak dapat dibatalkan melalui rute ini.', 409);
        }

        $canceledJobTitle = $application->jobVacancy->title ?? 'Pekerjaan';
        $canceledJobCompany = $application->jobVacancy->company ?? '';

        // Ubah status lamaran menjadi 'canceled'
        $application->status = 'canceled';
        $application->save();

        // Catat riwayat pembatalan pada linimasa pipeline
        $pipeline = PipelineTimeline::where('application_id', $application->id)->first();
        if ($pipeline) {
            $stages = $pipeline->stages ?? [];
            $stages[] = [
                'stage' => 'canceled',
                'status' => 'canceled',
                'entered_at' => now()->toIso8601String(),
                'completed_at' => now()->toIso8601String(),
                'notes' => 'Pengajuan lamaran dibatalkan secara mandiri oleh pihak pelamar.',
            ];
            $pipeline->current_stage = 'canceled';
            $pipeline->stages = $stages;
            $pipeline->save();
        }

        return $this->successResponse([
            'application_id' => $application->id,
            'job_title' => $canceledJobTitle,
            'company' => $canceledJobCompany,
            'status' => 'canceled',
            'canceled_at' => now()->toIso8601String(),
        ], "Pengajuan berkas lamaran untuk posisi {$canceledJobTitle} berhasil dibatalkan.");
    }
}
