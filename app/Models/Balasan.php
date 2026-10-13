<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balasan extends Model
{
    protected $table = 'balasan';
    protected $fillable = ['postingan_id', 'user_id', 'isi'];

    public function postingan() { return $this->belongsTo(Postingan::class); }
    public function penulis()   { return $this->belongsTo(User::class, 'user_id'); }
}
