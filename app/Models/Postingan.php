<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postingan extends Model
{
    protected $table = 'postingan';
    protected $fillable = ['user_id', 'judul', 'isi'];
 
    public function penulis() { return $this->belongsTo(User::class, 'user_id'); }
    public function balasan() { return $this->hasMany(Balasan::class); }
}
