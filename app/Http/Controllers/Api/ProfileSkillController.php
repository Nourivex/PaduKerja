<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class ProfileSkillController
 *
 * Mengelola fungsionalitas Paket D: Matriks Keahlian (Skills) Profil Pengguna.
 * Bagian dari Layanan Microservice: Auth & Profile Service (Penanggung Jawab: MUHAMMAD AFFIF).
 *
 * Pokok Bahasan Pembelajaran Mahasiswa:
 * 1. Penggunaan Method HTTP "PUT" untuk memperbarui seluruh kumpulan data keahlian (replace/update array).
 * 2. Otorisasi kepemilikan akun (User hanya boleh mengubah keahlian profilnya sendiri, kecuali Admin).
 * 3. Sanitasi dan pembersihan data array (menghapus duplikasi nama skill secara case-insensitive).
 */
class ProfileSkillController extends Controller
{
    use ApiResponse;

    /**
     * Paket D: Menyimpan atau memperbarui data daftar keahlian (skills) di profil pengguna.
     *
     * Method  : PUT
     * Endpoint: /api/users/{id}/skills  atau  /api/profile/skills
     * Headers : Authorization: Bearer <token>, Content-Type: application/json
     *
     * @param  Request   $request  Objek HTTP Request yang berisi payload array skills
     * @param  int|null  $id       ID pengguna spesifik (opsional)
     * @return JsonResponse        JSON envelope konfirmasi pembaruan keahlian
     */
    public function updateSkills(Request $request, ?int $id = null): JsonResponse
    {
        // 1. Ambil data pengguna yang sedang login dari JWT Token
        $currentUser = $request->attributes->get('auth_user') ?? $request->user();

        // 2. Cek apakah request menargetkan profil pengguna lain
        $targetUser = $currentUser;
        if ($id !== null && $currentUser && $currentUser->id !== $id) {
            // Hanya pengguna dengan peran 'admin' yang berhak mengubah skill orang lain
            if ($currentUser->role !== 'admin') {
                return $this->forbiddenResponse('Akses dilarang. Anda tidak memiliki wewenang untuk mengubah data profil milik pengguna lain.');
            }

            $targetUser = User::find($id);
            if (! $targetUser) {
                return $this->notFoundResponse('Pengguna target tidak ditemukan di dalam sistem.');
            }
        }

        // 3. Validasi struktur payload input
        $validator = Validator::make($request->all(), [
            'skills' => 'required|array|min:1',
            'skills.*' => 'required',
        ], [
            'skills.required' => 'Daftar keahlian (skills) wajib disertakan.',
            'skills.array' => 'Format data skills harus berupa array daftar keahlian.',
            'skills.min' => 'Minimal masukkan satu nama keahlian.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Format data keahlian tidak valid.');
        }

        // 4. Normalisasi dan sanitasi data keahlian dari masukan pengguna
        $rawSkills = $request->input('skills');
        $formattedSkills = [];

        foreach ($rawSkills as $skill) {
            if (is_string($skill)) {
                $trimmed = trim($skill);
                if ($trimmed !== '') {
                    $formattedSkills[] = $trimmed;
                }
            } elseif (is_array($skill) && ! empty($skill['name'])) {
                // Menangani jika input dikirim dalam format objek: [{"name": "PHP"}]
                $formattedSkills[] = trim($skill['name']);
            }
        }

        // 5. Hapus duplikasi nama keahlian yang serupa
        $uniqueSkills = array_values(array_unique($formattedSkills, SORT_REGULAR));

        // 6. Simpan array keahlian baru ke dalam kolom JSON di tabel database
        $targetUser->skills = $uniqueSkills;
        $targetUser->save();

        // 7. Kembalikan response HTTP 200 OK dengan format envelope standar
        return $this->successResponse([
            'user_id' => $targetUser->id,
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'skills' => $targetUser->skills,
            'skills_count' => count($targetUser->skills),
            'updated_at' => $targetUser->updated_at->toIso8601String(),
        ], 'Data keahlian (skills) pada profil pengguna berhasil diperbarui.');
    }
}
