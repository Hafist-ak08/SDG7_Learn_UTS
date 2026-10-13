<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opsi extends Model
{
    protected $table = 'opsi';
    protected $fillable = ['pertanyaan_id', 'teks', 'benar'];
    protected $casts = ['benar' => 'boolean'];
    
    public function pertanyaan() { return $this->belongsTo(Pertanyaan::class); }
}
