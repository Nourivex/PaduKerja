<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model JobVacancy
 *
 * Entitas data lowongan pekerjaan beserta kriteria keahlian dan bobotnya.
 * Bagian dari Layanan Microservice: Job Catalog Service.
 */
class JobVacancy extends Model
{
    use HasFactory;

    /**
     * Kolom-kolom yang dapat diisi secara massal (Mass Assignable).
     */
    protected $fillable = [
        'title',
        'company',
        'description',
        'location',
        'employment_type',
        'salary_min',
        'salary_max',
        'quota',
        'filled',
        'requirements', // Kriteria keahlian dan bobot kualifikasi (JSON)
        'status',       // open, closed
        'posted_by',    // ID Recruiter pembuat lowongan
        'expires_at',   // Batas akhir pendaftaran
    ];

    /**
     * Tipe konversi tipe data otomatis (Type Casting).
     */
    protected function casts(): array
    {
        return [
            'requirements' => 'array', // Otomatis mengubah JSON requirements menjadi array PHP
            'expires_at' => 'datetime',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
            'quota' => 'integer',
            'filled' => 'integer',
        ];
    }

    /**
     * Relasi: Lowongan ini dimiliki/diterbitkan oleh satu Recruiter (Belongs-to).
     */
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Relasi: Satu lowongan dapat menerima banyak berkas lamaran (One-to-Many).
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_vacancy_id');
    }
}
