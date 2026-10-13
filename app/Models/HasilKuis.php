<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilKuis extends Model
{
    protected $table = 'hasil_kuis';

    protected $fillable = [
        'user_id',
        'kuis_id',
        'kuis_judul',
        'skor',
        'skor_maks',
        'persentase',
        'jumlah_benar',
        'jumlah_pertanyaan',
        'tinjauan',
    ];

    protected $casts = [
        'tinjauan' => 'array',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function kuis() { return $this->belongsTo(Kuis::class); }
}
