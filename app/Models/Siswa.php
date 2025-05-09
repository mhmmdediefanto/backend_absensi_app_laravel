<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $guarded = ['id'];
    protected $table = 'siswas';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lokasiPrakerin()
    {
        return $this->belongsTo(LokasiPrakerin::class, 'lokasi_prakerin_id', 'id');
    }
}
