<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobVacancyController;
use App\Http\Controllers\Api\PipelineController;
use App\Http\Controllers\Api\ProfileSkillController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dokumentasi & Peta Rute REST API - Sistem PaduKerja
|--------------------------------------------------------------------------
|
| Mata Kuliah : Pemrograman Web Service (5537344 / 3 SKS) - Kelompok 4 UHN
| Standar     : RESTful JSON API (RFC 8259), HTTP Methods Semantics, JWT Bearer Token
|
| Pembagian Modul Referensi Praktikum:
| 📌 PAKET D: Akun & Keahlian (Auth & Profile Service - MUHAMMAD AFFIF)
|    - POST  /api/auth/login          : Login akun pengguna & generate token akses JWT
|    - POST  /api/auth/register       : Registrasi akun baru (applicant / recruiter)
|    - GET   /api/auth/me             : Mengambil profil lengkap user saat ini
|    - PUT   /api/profile/skills      : Menyimpan / update data keahlian (skills) di profil
|    - PUT   /api/users/{id}/skills   : Update keahlian pengguna berdasarkan ID
|
| 📌 PAKET A: Lowongan Kerja (Job Catalog Service - MUHAMAD FAHREN ANDREAN RANGKUTI)
|    - GET   /api/jobs                : Menampilkan & filter daftar lowongan kerja
|    - GET   /api/jobs/{id}           : Menampilkan detail satu lowongan kerja
|    - POST  /api/jobs                : Recruiter memposting lowongan kerja baru
|
| 📌 PAKET B: Lamaran & Scoring (Application & Matchmaking Service - NABE'ELA AYU N. T. Z.)
|    - GET   /api/applications        : Menampilkan daftar lamaran kerja
|    - GET   /api/applications/{id}   : Menampilkan detail berkas lamaran
|    - POST  /api/applications        : Mengirim lamaran + otomatis hitung skor kecocokan
|    - DELETE /api/applications/{id}  : Membatalkan pengajuan berkas lamaran
|
| 📌 PAKET C: Seleksi Pelamar (Recruitment Pipeline Service - MUHAMMAD YASIR ILHAM NABIL)
|    - GET   /api/pipelines/{id}      : Pelamar melihat riwayat linimasa proses lamaran
|    - PATCH /api/pipelines/{id}/status : Recruiter update status / tahap seleksi pelamar
|
*/

// =========================================================================
// 1. Pemeriksaan Status Kesehatan Layanan API (Health Check Endpoint)
// =========================================================================
Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Layanan REST API PaduKerja beroperasi normal dan siap menerima request.',
        'data' => [
            'aplikasi' => 'PaduKerja Core API',
            'versi' => '1.0.0',
            'modul_tersedia' => [
                'Paket A: Lowongan Kerja' => ['GET /api/jobs', 'POST /api/jobs'],
                'Paket B: Lamaran & Scoring' => ['POST /api/applications', 'DELETE /api/applications/{id}'],
                'Paket C: Seleksi Pelamar' => ['PATCH /api/pipelines/{id}/status', 'GET /api/pipelines/{id}'],
                'Paket D: Akun & Keahlian' => ['POST /api/auth/login', 'PUT /api/profile/skills'],
            ],
        ],
        'meta' => [
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
        ],
    ]);
});

// =========================================================================
// 2. 📌 PAKET D: Akun & Keahlian (Auth & Profile Service)
// =========================================================================
Route::prefix('auth')->group(function () {
    // Rute Publik (Tanpa Token JWT)
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Rute Terproteksi (Wajib Header: Authorization: Bearer <token>)
    Route::get('/me', [AuthController::class, 'me'])->middleware('jwt.auth');
});

Route::middleware('jwt.auth')->group(function () {
    // Method PUT: Menyimpan atau memperbarui kumpulan data keahlian (skills) di profil pengguna
    Route::put('/profile/skills', [ProfileSkillController::class, 'updateSkills']);
    Route::put('/users/{id}/skills', [ProfileSkillController::class, 'updateSkills']);
});

// =========================================================================
// 3. 📌 PAKET A: Lowongan Kerja (Job Catalog Service)
// =========================================================================
// Method GET: Menampilkan dan memfilter lowongan (Dapat diakses publik atau terotentikasi)
Route::get('/jobs', [JobVacancyController::class, 'index']);
Route::get('/jobs/{id}', [JobVacancyController::class, 'show']);

// Method POST: Khusus peran 'recruiter' untuk menerbitkan lowongan pekerjaan baru
Route::post('/jobs', [JobVacancyController::class, 'store'])->middleware('jwt.auth:recruiter');

// =========================================================================
// 4. 📌 PAKET B: Lamaran & Scoring (Application & Matchmaking Service)
// =========================================================================
Route::middleware('jwt.auth')->group(function () {
    // Melihat daftar lamaran milik sendiri
    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::get('/applications/{id}', [ApplicationController::class, 'show']);

    // Method POST: Mengirim berkas lamaran + memicu kalkulasi skor kecocokan secara otomatis
    Route::post('/applications', [ApplicationController::class, 'store']);

    // Method DELETE: Membatalkan pengajuan lamaran yang belum disetujui/ditolak
    Route::delete('/applications/{id}', [ApplicationController::class, 'destroy']);
});

// =========================================================================
// 5. 📌 PAKET C: Seleksi Pelamar (Recruitment Pipeline Service)
// =========================================================================
Route::middleware('jwt.auth')->group(function () {
    // Method GET: Pelamar melihat riwayat linimasa dan status tahapan seleksinya
    Route::get('/pipelines/{applicationId}', [PipelineController::class, 'showTimeline']);

    // Method PATCH: Khusus Recruiter untuk memajukan tahapan (Screening -> Assessment -> Interview -> Offering)
    Route::patch('/pipelines/{applicationId}/status', [PipelineController::class, 'updateStatus'])->middleware('jwt.auth:recruiter');
    Route::patch('/applications/{id}/stage', [PipelineController::class, 'updateStatus'])->middleware('jwt.auth:recruiter');
});
