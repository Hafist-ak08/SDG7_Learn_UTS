<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    protected $table = 'refresh_tokens';

    protected $fillable = [
        'user_id',
        'token',
        'expired_at',
        'dipakai_pada',
    ];

    protected $casts = [
        'expired_at'   => 'datetime',
        'dipakai_pada' => 'datetime',
    ];

    /**
     * Relasi ke User pemilik token.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
