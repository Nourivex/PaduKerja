<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Trait ApiResponse
 *
 * Trait ini berfungsi sebagai helper global untuk standardisasi JSON Envelope
 * pada seluruh response REST API sistem PaduKerja.
 *
 * Sesuai kesepakatan dokumen API_CONTRACT_STANDARDS.md:
 * Setiap response API wajib memiliki struktur pembungkus (envelope):
 * - status  : "success" atau "error"
 * - message : Penjelasan pesan yang mudah dipahami manusia
 * - data    : Payload data utama (object / array / null)
 * - meta    : Metadata pendukung (timestamp ISO8601, versi API, pagination)
 * - errors  : Rincian error validasi per field (hanya muncul saat 422 / error)
 */
trait ApiResponse
{
    /**
     * Mengirim response sukses standar (HTTP 200 OK atau custom code).
     *
     * @param  mixed   $data        Data payload yang akan dikembalikan ke client
     * @param  string  $message     Pesan keberhasilan operasi
     * @param  int     $statusCode  Kode status HTTP (default: 200 OK)
     * @param  array   $extraMeta   Metadata tambahan (seperti pagination atau filter info)
     */
    protected function successResponse(mixed $data = null, string $message = 'Operasi berhasil diproses.', int $statusCode = 200, array $extraMeta = []): JsonResponse
    {
        $meta = array_merge([
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
        ], $extraMeta);

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ], $statusCode);
    }

    /**
     * Mengirim response resource baru berhasil dibuat (HTTP 201 Created).
     * Biasa digunakan setelah operasi POST pembuatan data baru (misal: posting lowongan, kirim lamaran).
     *
     * @param  mixed   $data       Resource yang baru saja dibuat
     * @param  string  $message    Pesan konfirmasi pembuatan data
     * @param  array   $extraMeta  Metadata tambahan
     */
    protected function createdResponse(mixed $data = null, string $message = 'Data baru berhasil dibuat.', array $extraMeta = []): JsonResponse
    {
        return $this->successResponse($data, $message, 201, $extraMeta);
    }

    /**
     * Mengirim response error standar (HTTP 4xx atau 5xx).
     *
     * @param  string  $message     Pesan kegagalan/kesalahan
     * @param  int     $statusCode  Kode status HTTP (default: 400 Bad Request)
     * @param  mixed   $errors      Detail error (opsional)
     */
    protected function errorResponse(string $message, int $statusCode = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message,
            'data' => null,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'version' => '1.0.0',
            ],
        ];

        // Jika terdapat detail validasi atau error spesifik, sertakan di object 'errors'
        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Mengirim response gagal validasi input (HTTP 422 Unprocessable Entity).
     * Digunakan ketika input dari client tidak lolos aturan validasi (misal: email kosong, format salah).
     *
     * @param  mixed   $errors   Daftar error per field dari Validator
     * @param  string  $message  Pesan ringkasan kegagalan validasi
     */
    protected function validationErrorResponse(mixed $errors, string $message = 'Validasi data masukan gagal.'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Mengirim response tidak terautentikasi (HTTP 401 Unauthorized).
     * Digunakan saat client tidak menyertakan token JWT, token salah, atau sudah kedaluwarsa (expired).
     *
     * @param  string  $message  Pesan penolakan autentikasi
     */
    protected function unauthorizedResponse(string $message = 'Akses ditolak. Token autentikasi tidak valid atau belum disertakan.'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Mengirim response hak akses tidak memadai (HTTP 403 Forbidden).
     * Digunakan saat identitas client dikenali (token valid), tetapi peran (role) pengguna tidak berhak
     * mengakses resource tersebut (misal: pelamar mencoba memposting lowongan kerja).
     *
     * @param  string  $message  Pesan penolakan hak akses
     */
    protected function forbiddenResponse(string $message = 'Akses dilarang. Akun Anda tidak memiliki hak otorisasi untuk tindakan ini.'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Mengirim response resource tidak ditemukan (HTTP 404 Not Found).
     * Digunakan saat ID data yang diminta tidak ada di database.
     *
     * @param  string  $message  Pesan bahwa data tidak ditemukan
     */
    protected function notFoundResponse(string $message = 'Data yang diminta tidak ditemukan.'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }
}
