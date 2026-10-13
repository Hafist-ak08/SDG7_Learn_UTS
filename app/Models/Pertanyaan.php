<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pertanyaan extends Model
{
    protected $table = 'pertanyaan';
    protected $fillable = ['kuis_id', 'urutan', 'teks', 'poin', 'penjelasan'];
 
    public function kuis() { return $this->belongsTo(Kuis::class); }
    public function opsi() { return $this->hasMany(Opsi::class); }
}
