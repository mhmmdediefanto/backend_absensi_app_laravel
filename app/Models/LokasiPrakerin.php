<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiPrakerin extends Model
{
    protected $table = 'lokasi_prakerins';
    protected $guarded = ['id'];


    public function total()
    {
        return $this->count();
    }

    public function siswa()
    {
        return $this->hasMany(User::class);
    }

    public function jadwalPrakerin()
    {
        return $this->hasMany(JadwalPrakerin::class);
    }
}
