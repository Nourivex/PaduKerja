<?php

namespace App\Services;

/**
 * Class MatchmakingService
 *
 * Layanan Mesin Pemadanan Keahlian Otomatis (Automated Skill-Matchmaking Engine).
 *
 * Penjelasan Formula untuk Pembelajaran Mahasiswa:
 * Sistem menggunakan algoritma "Weighted Jaccard Similarity" untuk menghitung
 * persentase kesesuaian antara daftar keahlian pelamar kerja dan syarat kompetensi lowongan.
 *
 * Rumus Matematis:
 *                          Σ (bobot keahlian pelamar yang cocok)
 *       Skor Kecocokan = ---------------------------------------- x 100%
 *                          Σ (total bobot seluruh syarat lowongan)
 *
 * Tingkatan Bobot Kualifikasi:
 * - required     : Bobot 3 (Sangat mutlak/wajib dipenuhi)
 * - important    : Bobot 2 (Penting dan menjadi nilai utama)
 * - nice_to_have : Bobot 1 (Nilai tambah / pendukung)
 *
 * Kategori Hasil Penilaian:
 * - 80% - 100% : excellent       (Sangat cocok, lolos otomatis ke tahap berikutnya)
 * - 50% - 79%  : good            (Cukup cocok, masuk peninjauan recruiter manual)
 * - 0% - 49%   : under_qualified (Belum memenuhi kualifikasi minimal)
 */
class MatchmakingService
{
    /**
     * Menghitung skor kecocokan antara keahlian kandidat pelamar dan syarat lowongan kerja.
     *
     * @param  array  $candidateSkills  Array daftar nama keahlian pelamar (cth: ['PHP', 'Laravel', 'MySQL'])
     * @param  array  $jobRequirements  Array kualifikasi lowongan (cth: [['skill' => 'PHP', 'weight' => 3, 'level' => 'required']])
     * @return array                    Hasil kalkulasi lengkap beserta rincian matematisnya
     */
    public function calculate(array $candidateSkills, array $jobRequirements): array
    {
        // Normalisasi keahlian kandidat ke huruf kecil untuk pencocokan case-insensitive
        $normalizedCandidate = array_map(function ($s) {
            if (is_array($s)) {
                return strtolower($s['name'] ?? '');
            }

            return strtolower((string) $s);
        }, $candidateSkills);

        // Jika lowongan tidak menentukan syarat keahlian khusus, beri skor maksimal 100%
        if (empty($jobRequirements)) {
            return [
                'score' => 100.0,
                'category' => 'excellent',
                'matched_skills' => $candidateSkills,
                'missing_skills' => [],
            ];
        }

        $totalRequiredWeight = 0; // Total akumulasi bobot semua syarat
        $matchedWeight = 0;       // Akumulasi bobot keahlian yang berhasil dicocokkan
        $matchedSkills = [];      // Daftar keahlian yang cocok
        $missingSkills = [];      // Daftar keahlian yang belum dimiliki pelamar

        // Iterasi setiap syarat keahlian yang diminta oleh lowongan
        foreach ($jobRequirements as $req) {
            $skillName = $req['skill'] ?? '';
            $level = strtolower($req['level'] ?? 'required');

            // Tentukan bobot standar berdasarkan level kualifikasi
            $weight = match ($level) {
                'required' => 3,
                'important' => 2,
                'nice_to_have', 'nice-to-have' => 1,
                default => (int) ($req['weight'] ?? 2),
            };

            // Jika dalam data terdapat bobot kustom positif, utamakan bobot tersebut
            if (isset($req['weight']) && is_numeric($req['weight']) && (int) $req['weight'] > 0) {
                $weight = (int) $req['weight'];
            }

            $totalRequiredWeight += $weight;

            // Periksa apakah pelamar memiliki keahlian tersebut
            if (in_array(strtolower($skillName), $normalizedCandidate, true)) {
                $matchedWeight += $weight;
                $matchedSkills[] = $skillName;
            } else {
                $missingSkills[] = $skillName;
            }
        }

        // Hitung persentase skor akhir (dibulatkan 2 angka di belakang koma)
        $score = $totalRequiredWeight > 0 ? round(($matchedWeight / $totalRequiredWeight) * 100, 2) : 0.0;

        // Tentukan kategori kelayakan berdasarkan skor
        $category = match (true) {
            $score >= 80.0 => 'excellent',
            $score >= 50.0 => 'good',
            default => 'under_qualified',
        };

        return [
            'score' => $score,
            'category' => $category,
            'matched_skills' => $matchedSkills,
            'missing_skills' => $missingSkills,
            'calculation_details' => [
                'matched_weight' => $matchedWeight,
                'total_weight' => $totalRequiredWeight,
                'formula' => 'Skor = (Σ Bobot Cocok / Σ Total Bobot Syarat) x 100%',
            ],
        ];
    }
}
