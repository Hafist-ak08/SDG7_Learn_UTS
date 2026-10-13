<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kuis extends Model
{
    protected $table = 'kuis';   // Laravel menebak 'kuises', jadi set manual
    protected $fillable = ['judul', 'deskripsi'];

    public function pertanyaan() { return $this->hasMany(Pertanyaan::class)->orderBy('urutan'); }
    public function hasil()      { return $this->hasMany(HasilKuis::class); }
}