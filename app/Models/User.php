<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Kolom yang boleh diisi secara massal.
     */
    protected $fillable = [
        'nama',
        'email',
        'password',
        'pekerjaan',
        'usia',
        'role',
    ];

    /**
     * Kolom yang disembunyikan dari serialisasi.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Tipe casting kolom.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // Relasi ke Forum Diskusi (postingan)
    public function postingan()
    {
        return $this->hasMany(Postingan::class, 'user_id');
    }

    // Relasi ke Forum Diskusi (balasan)
    public function balasan()
    {
        return $this->hasMany(Balasan::class, 'user_id');
    }

    // Relasi ke Hasil Kuis
    public function hasilKuis()
    {
        return $this->hasMany(HasilKuis::class, 'user_id');
    }
}