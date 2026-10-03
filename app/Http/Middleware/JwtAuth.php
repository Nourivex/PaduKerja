<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class JwtAuth
 *
 * Middleware untuk memvalidasi token JWT dan melakukan otorisasi berbasis peran (Role-Based Access Control).
 *
 * Penjelasan Alur Kerja Middleware untuk Mahasiswa:
 * 1. Mencegat (intercept) setiap HTTP Request yang masuk sebelum mencapai Controller.
 * 2. Memeriksa keberadaan header 'Authorization' berformat 'Bearer <token>'.
 * 3. Memverifikasi keabsahan tanda tangan dan masa kedaluwarsa token melalui JwtService.
 * 4. Jika valid, mengambil identitas pengguna dari database dan melampirkannya ke objek Request ($request).
 * 5. Jika rute mensyaratkan peran spesifik (misal: recruiter), middleware memeriksa apakah pengguna berhak.
 * 6. Jika gagal di tahap mana pun, langsung mengembalikan JSON Envelope error 401 atau 403 tanpa mengeksekusi Controller.
 */
class JwtAuth
{
    use ApiResponse;

    public function __construct(protected JwtService $jwtService)
    {
    }

    /**
     * Menangani request yang masuk.
     *
     * @param  Request      $request       Objek request HTTP saat ini
     * @param  Closure      $next          Fungsi callback menuju middleware berikutnya / controller
     * @param  string|null  $requiredRole  Peran wajib yang disyaratkan oleh rute (opsional: recruiter/admin)
     */
    public function handle(Request $request, Closure $next, ?string $requiredRole = null): Response
    {
        // 1. Ambil header Authorization dari request HTTP
        $header = $request->header('Authorization');

        // Periksa apakah format header sesuai standar: "Bearer <token>"
        if (! $header || ! preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $this->unauthorizedResponse('Akses ditolak. Header "Authorization: Bearer <token>" wajib disertakan pada endpoint ini.');
        }

        $token = $matches[1];

        // 2. Dekode dan verifikasi isi token JWT
        $payload = $this->jwtService->decodeToken($token);

        if (! $payload || ! isset($payload->sub)) {
            return $this->unauthorizedResponse('Akses ditolak. Token akses tidak valid atau telah kedaluwarsa.');
        }

        // 3. Cari pengguna berdasarkan ID (sub) yang tersimpan di dalam token
        $user = User::find($payload->sub);

        if (! $user) {
            return $this->unauthorizedResponse('Pengguna pemilik token ini tidak ditemukan dalam database.');
        }

        // 4. Pengecekan Otorisasi Peran (Role Check) jika ditentukan di route
        if ($requiredRole !== null && $user->role !== $requiredRole && $user->role !== 'admin') {
            return $this->forbiddenResponse("Akses ditolak. Tindakan ini secara khusus memerlukan hak akses peran '{$requiredRole}'. Peran Anda saat ini: '{$user->role}'.");
        }

        // 5. Lampirkan pengguna ke request agar dapat diakses di Controller melalui $request->user()
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('auth_user', $user);

        // Teruskan request ke Controller tujuan
        return $next($request);
    }
}
