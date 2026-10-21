<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Glosarium extends Model
{
    protected $table = 'glosarium';

    protected $fillable = ['istilah', 'definisi', 'kategori', 'status'];
}
