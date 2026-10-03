<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\PipelineTimeline;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class PipelineController
 *
 * Mengelola fungsionalitas Paket C: Pelacakan Linimasa & Tahapan Seleksi Pelamar.
 * Bagian dari Layanan Microservice: Recruitment Pipeline Service (Penanggung Jawab: MUHAMMAD YASIR ILHAM NABIL).
 *
 * Pokok Bahasan Pembelajaran Mahasiswa:
 * 1. Method PATCH (Partial Update): Berbeda dari PUT yang mengganti seluruh resource, PATCH digunakan untuk
 *    memperbarui status sebagian field resource (dalam hal ini, memajukan tahapan seleksi kandidat).
 * 2. Alur Transisi Status Seleksi Bertahap (Recruitment Pipeline Lifecycle):
 *    Screening  -->  Assessment  -->  Interview  -->  Offering / Rejected
 * 3. Pencatatan Jejak Waktu Kronologis (Audit Trail / Timeline History):
 *    Setiap pergantian tahap mencatat waktu masuk (entered_at), waktu selesai (completed_at),
 *    catatan recruiter (notes), serta detail jadwal wawancara (interview_schedule).
 * 4. Transparansi Seleksi bagi Pelamar: Pelamar dapat memantau status secara langsung melalui Method GET.
 */
class PipelineController extends Controller
{
    use ApiResponse;

    /**
     * Paket C: Recruiter memperbarui status atau memajukan tahapan seleksi pelamar.
     *
     * Method  : PATCH
     * Endpoint: /api/pipelines/{applicationId}/status
     * Headers : Authorization: Bearer <token_recruiter>, Content-Type: application/json
     *
     * @param  Request       $request        Data tahapan baru, status, catatan, dan jadwal wawancara
     * @param  int           $applicationId  ID berkas lamaran yang diperbarui
     * @return JsonResponse                 JSON envelope konfirmasi pembaruan tahap seleksi
     */
    public function updateStatus(Request $request, int $applicationId): JsonResponse
    {
        // 1. Ambil data recruiter yang sedang terotentikasi
        $currentUser = $request->attributes->get('auth_user') ?? $request->user();

        // 2. Pemeriksaan peran: Hanya Recruiter atau Admin yang berhak memajukan tahapan seleksi
        if (! $currentUser || ($currentUser->role !== 'recruiter' && $currentUser->role !== 'admin')) {
            return $this->forbiddenResponse('Akses dilarang. Hanya akun Recruiter yang memiliki wewenang memperbarui tahapan seleksi pelamar.');
        }

        // 3. Validasi input tahapan
        $validator = Validator::make($request->all(), [
            'stage' => 'required|string|in:screening,assessment,interview,offering,rejected',
            'status' => 'nullable|string|in:in_progress,passed,rejected',
            'notes' => 'nullable|string|max:1000',
            'interview_schedule' => 'nullable|array',
            'interview_schedule.datetime' => 'nullable|date',
            'interview_schedule.type' => 'nullable|string|in:online,offline',
            'interview_schedule.meeting_url' => 'nullable|string',
            'interview_schedule.interviewer' => 'nullable|string',
        ], [
            'stage.required' => 'Tahapan seleksi (stage) wajib dipilih.',
            'stage.in' => 'Pilihan tahapan yang sah: screening, assessment, interview, offering, rejected.',
            'status.in' => 'Pilihan status tahap yang sah: in_progress, passed, rejected.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Data tahapan seleksi yang dikirim tidak valid.');
        }

        // 4. Cari berkas lamaran berdasarkan ID
        $application = Application::with('jobVacancy:id,title,company')->find($applicationId);

        if (! $application) {
            return $this->notFoundResponse("Data lamaran dengan nomor ID {$applicationId} tidak ditemukan.");
        }

        // 5. Ambil atau buat record linimasa pipeline jika belum tersedia
        $pipeline = PipelineTimeline::firstOrCreate(
            ['application_id' => $applicationId],
            [
                'current_stage' => 'screening',
                'stages' => [
                    [
                        'stage' => 'screening',
                        'status' => 'in_progress',
                        'entered_at' => now()->toIso8601String(),
                        'completed_at' => null,
                        'notes' => 'Berkas lamaran diterima dan memasuki tahap screening awal.',
                    ],
                ],
            ]
        );

        $newStage = $request->input('stage');
        $stageStatus = $request->input('status', 'in_progress');
        $notes = $request->input('notes');
        $interviewSchedule = $request->input('interview_schedule');

        $stages = $pipeline->stages ?? [];

        // 6. Jika berpindah ke tahapan baru, selesaikan (tutup waktu) tahap sebelumnya
        if ($pipeline->current_stage !== $newStage) {
            foreach ($stages as &$stg) {
                if ($stg['stage'] === $pipeline->current_stage && empty($stg['completed_at'])) {
                    $stg['completed_at'] = now()->toIso8601String();
                    if ($stg['status'] === 'in_progress') {
                        $stg['status'] = 'passed';
                    }
                }
            }
            unset($stg);

            // Tambahkan entri tahapan baru ke dalam array linimasa
            $newEntry = [
                'stage' => $newStage,
                'status' => $stageStatus,
                'entered_at' => now()->toIso8601String(),
                'completed_at' => ($stageStatus === 'passed' || $newStage === 'rejected') ? now()->toIso8601String() : null,
                'notes' => $notes,
            ];

            // Jika tahapan adalah wawancara (interview), lampirkan informasi jadwal
            if ($newStage === 'interview' && ! empty($interviewSchedule)) {
                $newEntry['interview_schedule'] = $interviewSchedule;
            }

            $stages[] = $newEntry;
        } else {
            // Jika masih di tahap yang sama, cukup perbarui catatan atau status tahap saat ini
            $lastIndex = count($stages) - 1;
            if ($lastIndex >= 0) {
                $stages[$lastIndex]['status'] = $stageStatus;
                if ($notes) {
                    $stages[$lastIndex]['notes'] = $notes;
                }
                if ($stageStatus === 'passed' || $newStage === 'rejected') {
                    $stages[$lastIndex]['completed_at'] = now()->toIso8601String();
                }
                if ($newStage === 'interview' && ! empty($interviewSchedule)) {
                    $stages[$lastIndex]['interview_schedule'] = array_merge(
                        $stages[$lastIndex]['interview_schedule'] ?? [],
                        $interviewSchedule
                    );
                }
            }
        }

        // 7. Simpan pembaruan ke tabel database
        $pipeline->current_stage = $newStage;
        $pipeline->stages = $stages;
        $pipeline->save();

        // 8. Sinkronisasikan status utama berkas lamaran
        $application->status = $newStage;
        $application->save();

        // 9. Kembalikan response HTTP 200 OK dengan data riwayat linimasa terbaru
        return $this->successResponse([
            'application_id' => $application->id,
            'job' => [
                'title' => $application->jobVacancy->title ?? '',
                'company' => $application->jobVacancy->company ?? '',
            ],
            'current_stage' => $pipeline->current_stage,
            'stage_status' => $stageStatus,
            'stages' => $pipeline->stages,
            'updated_at' => $pipeline->updated_at->toIso8601String(),
        ], "Tahapan proses seleksi pelamar berhasil diperbarui ke tahap '{$newStage}'.");
    }

    /**
     * Paket C: Pelamar melihat riwayat linimasa proses lamaran kerjanya.
     *
     * Method  : GET
     * Endpoint: /api/pipelines/{applicationId}
     * Headers : Authorization: Bearer <token_pelamar>
     *
     * @param  int           $applicationId  ID berkas lamaran yang ingin dilihat riwayatnya
     * @param  Request       $request        Objek HTTP Request
     * @return JsonResponse                 JSON envelope riwayat linimasa proses seleksi
     */
    public function showTimeline(int $applicationId, Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('auth_user') ?? $request->user();

        // 1. Ambil data lamaran beserta relasi lowongan dan pengguna
        $application = Application::with([
            'jobVacancy:id,title,company,location',
            'applicant:id,name,email',
            'pipelineTimeline',
        ])->find($applicationId);

        if (! $application) {
            return $this->notFoundResponse("Data lamaran dengan nomor ID {$applicationId} tidak ditemukan.");
        }

        // 2. Pemeriksaan otorisasi: Pelamar hanya boleh melihat linimasa berkas lamaran miliknya sendiri
        if ($currentUser && $currentUser->role === 'applicant' && $application->user_id !== $currentUser->id) {
            return $this->forbiddenResponse('Akses dilarang. Anda tidak memiliki izin untuk memantau linimasa lamaran kandidat lain.');
        }

        $pipeline = $application->pipelineTimeline;

        // Ambil daftar riwayat tahapan
        $stages = $pipeline?->stages ?? [
            [
                'stage' => 'screening',
                'status' => 'in_progress',
                'entered_at' => $application->created_at->toIso8601String(),
                'completed_at' => null,
                'notes' => 'Berkas lamaran dalam proses peninjauan awal.',
            ],
        ];

        // 3. Hitung ringkasan statistik linimasa proses seleksi
        $firstEntered = ! empty($stages[0]['entered_at']) ? Carbon::parse($stages[0]['entered_at']) : $application->created_at;
        $totalDays = max(1, (int) $firstEntered->diffInDays(now()));
        $completedCount = count(array_filter($stages, fn ($s) => ! empty($s['completed_at'])));

        // 4. Kembalikan response HTTP 200 OK dengan detail linimasa yang transparan
        return $this->successResponse([
            'application_id' => $application->id,
            'job' => [
                'id' => $application->jobVacancy->id ?? null,
                'title' => $application->jobVacancy->title ?? '',
                'company' => $application->jobVacancy->company ?? '',
                'location' => $application->jobVacancy->location ?? '',
            ],
            'applicant' => [
                'name' => $application->applicant->name ?? '',
                'match_score' => $application->match_score,
                'match_category' => $application->match_category,
            ],
            'current_stage' => $pipeline?->current_stage ?? 'screening',
            'stages' => $stages,
            'timeline_summary' => [
                'days_active' => $totalDays,
                'stages_completed' => $completedCount,
                'stages_total' => 4, // 4 Tahapan Utama: Screening, Assessment, Interview, Offering
            ],
        ], 'Riwayat linimasa proses lamaran berhasil dimuat.');
    }
}
