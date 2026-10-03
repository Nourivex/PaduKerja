<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JwtService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Class AuthController
 *
 * Mengelola fungsionalitas Paket D: Autentikasi Pengguna & Sesi Akses Token.
 * Bagian dari Layanan Microservice: Auth & Profile Service (Penanggung Jawab: MUHAMMAD AFFIF).
 *
 * Pokok Bahasan Pembelajaran Mahasiswa:
 * 1. Menerima payload JSON dari body HTTP Request.
 * 2. Melakukan validasi input menggunakan class Validator Laravel.
 * 3. Memeriksa kecocokan hash sandi pengguna (Bcrypt).
 * 4. Menerbitkan tanda pengenal digital stateless (JWT Access Token).
 * 5. Mengembalikan envelope format JSON standar.
 */
class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(protected JwtService $jwtService)
    {
    }

    /**
     * Paket D: Login pengguna & generate token akses JWT.
     *
     * Method  : POST
     * Endpoint: /api/auth/login
     * Headers : Content-Type: application/json, Accept: application/json
     *
     * @param  Request       $request  Data HTTP Request (email & password)
     * @return JsonResponse           JSON envelope berisi token dan profil dasar
     */
    public function login(Request $request): JsonResponse
    {
        // 1. Validasi input: email wajib berformat valid, password minimal 6 karakter
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal berjumlah 6 karakter.',
        ]);

        // Jika validasi gagal, kembalikan HTTP 422 Unprocessable Entity
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Validasi login gagal.');
        }

        // 2. Cari data pengguna di database berdasarkan alamat email
        $user = User::where('email', $request->email)->first();

        // 3. Verifikasi apakah user ditemukan dan apakah hash password cocok
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // Mengembalikan HTTP 401 Unauthorized jika kredensial salah
            return $this->errorResponse('Kredensial tidak valid. Alamat email atau kata sandi salah.', 401);
        }

        // 4. Buat token akses JWT menggunakan JwtService
        $token = $this->jwtService->generateToken($user);

        // 5. Kembalikan response sukses HTTP 200 OK dengan format envelope
        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 86400 * 7, // Berlaku selama 7 hari (dalam detik)
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'location' => $user->location,
                'skills' => $user->skills ?? [],
            ],
        ], 'Login berhasil. Token otentikasi JWT berhasil diterbitkan.');
    }

    /**
     * Mendaftarkan akun pengguna baru (Pelamar atau Recruiter).
     *
     * Method  : POST
     * Endpoint: /api/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'nullable|in:applicant,recruiter,admin',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:100',
            'skills' => 'nullable|array',
        ], [
            'name.required' => 'Nama lengkap pengguna wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email ini sudah terdaftar dalam sistem.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'role.in' => 'Pilihan peran yang diperbolehkan: applicant, recruiter, admin.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Pendaftaran akun gagal karena data tidak valid.');
        }

        // Buat record pengguna baru dengan password yang dienkripsi (Hash Bcrypt)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'applicant',
            'phone' => $request->phone,
            'location' => $request->location,
            'skills' => $request->skills ?? [],
        ]);

        // Terbitkan token akses langsung setelah pendaftaran berhasil
        $token = $this->jwtService->generateToken($user);

        // Kembalikan HTTP 201 Created
        return $this->createdResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'skills' => $user->skills,
            ],
        ], 'Pendaftaran akun pengguna baru berhasil.');
    }

    /**
     * Menampilkan data profil lengkap pengguna yang sedang aktif (terotentikasi).
     *
     * Method  : GET
     * Endpoint: /api/auth/me
     * Headers : Authorization: Bearer <token>
     */
    public function me(Request $request): JsonResponse
    {
        // Ambil data user yang telah dilampirkan oleh Middleware JwtAuth
        $user = $request->attributes->get('auth_user') ?? $request->user();

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'bio' => $user->bio,
            'location' => $user->location,
            'skills' => $user->skills ?? [],
        ], 'Data profil pengguna berhasil dimuat.');
    }
}
