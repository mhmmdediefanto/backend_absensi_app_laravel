<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensis extends Model
{
    protected $table = 'absensis';
    protected $guarded = ['id'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'user_id', 'user_id');
    }
}
