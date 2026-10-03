<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model Application
 *
 * Entitas data berkas lamaran kerja, hasil perhitungan skor pemadanan keahlian, dan status seleksi.
 * Bagian dari Layanan Microservice: Application & Matchmaking Service.
 */
class Application extends Model
{
    use HasFactory;

    /**
     * Kolom-kolom yang dapat diisi secara massal (Mass Assignable).
     */
    protected $fillable = [
        'user_id',
        'job_vacancy_id',
        'cover_letter',
        'cv_file_path',
        'match_score',    // Nilai kecocokan keahlian (0.00 s/d 100.00%)
        'match_category', // excellent, good, under_qualified
        'matched_skills', // Keahlian yang cocok (JSON)
        'missing_skills', // Keahlian lowongan yang belum dimiliki (JSON)
        'status',         // submitted, in_review, canceled, interview, offering, rejected
    ];

    /**
     * Tipe konversi tipe data otomatis (Type Casting).
     */
    protected function casts(): array
    {
        return [
            'match_score' => 'float',
            'matched_skills' => 'array',
            'missing_skills' => 'array',
        ];
    }

    /**
     * Relasi: Lamaran ini diajukan oleh seorang Pelamar (User).
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi: Lamaran ini ditujukan pada satu Lowongan Kerja (JobVacancy).
     */
    public function jobVacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class, 'job_vacancy_id');
    }

    /**
     * Relasi: Lamaran ini memiliki satu linimasa tahapan pipeline (One-to-One).
     */
    public function pipelineTimeline(): HasOne
    {
        return $this->hasOne(PipelineTimeline::class, 'application_id');
    }
}
