<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilKuis extends Model
{
    protected $table = 'hasil_kuis';
    protected $fillable = ['user_id', 'kuis_id', 'skor', 'skor_maks', 'jumlah_benar', 'tinjauan'];
    protected $casts = ['tinjauan' => 'array'];

    public function user() { return $this->belongsTo(User::class); }
    public function kuis() { return $this->belongsTo(Kuis::class); }
}
