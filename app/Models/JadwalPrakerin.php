<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPrakerin extends Model
{
    protected $guarded = ['id'];
    protected $table = 'jadwal_prakerins';

    public function lokasiPrakerin()
    {
        return $this->belongsTo(LokasiPrakerin::class, 'lokasi_prakerin_id');
    }
}
