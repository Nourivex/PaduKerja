<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model User
 *
 * Entitas data pengguna sistem PaduKerja (pelamar, recruiter, admin).
 * Bagian dari Layanan Microservice: Auth & Profile Service.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Kolom-kolom yang dapat diisi secara massal (Mass Assignable).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',     // applicant, recruiter, admin
        'phone',    // nomor telepon
        'bio',      // biografi singkat
        'location', // lokasi kota
        'skills',   // array daftar keahlian
    ];

    /**
     * Kolom-kolom yang disembunyikan saat serialisasi JSON.
     * Menjaga keamanan agar hash password dan remember token tidak bocor ke client.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Tipe konversi tipe data otomatis (Type Casting) atribut Eloquent.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'skills' => 'array', // Otomatis mengubah data JSON database menjadi PHP array
        ];
    }

    /**
     * Relasi: Satu Recruiter dapat menerbitkan banyak lowongan pekerjaan (One-to-Many).
     */
    public function jobVacancies(): HasMany
    {
        return $this->hasMany(JobVacancy::class, 'posted_by');
    }

    /**
     * Relasi: Satu Pelamar dapat mengajukan banyak berkas lamaran (One-to-Many).
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'user_id');
    }
}
