<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model PipelineTimeline
 *
 * Entitas linimasa tahapan seleksi pelamar secara transparan dan kronologis.
 * Bagian dari Layanan Microservice: Recruitment Pipeline Service.
 */
class PipelineTimeline extends Model
{
    use HasFactory;

    /**
     * Kolom-kolom yang dapat diisi secara massal (Mass Assignable).
     */
    protected $fillable = [
        'application_id',
        'current_stage', // screening, assessment, interview, offering, rejected, canceled
        'stages',        // Array riwayat tahapan seleksi beserta timestamp dan catatan (JSON)
    ];

    /**
     * Tipe konversi tipe data otomatis (Type Casting).
     */
    protected function casts(): array
    {
        return [
            'stages' => 'array', // Menyimpan riwayat jejak waktu tahapan sebagai array JSON
        ];
    }

    /**
     * Relasi: Linimasa ini terhubung pada satu berkas lamaran (Belongs-to Application).
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }
}
