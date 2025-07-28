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

    public function total()
    {
        return $this->count();
    }

    public function lokasiPrakerin()
    {
        return $this->belongsTo(LokasiPrakerin::class, 'lokasi_prakerin_id', 'id');
    }

    public function guruPembimbingLokasi()
    {
        return $this->belongsToMany(Guru::class, 'pembimbing_siswas', 'siswa_id', 'guru_id');
    }

    public static function countSiswaLokasi()
    {
        return self::whereNotNull('lokasi_prakerin_id')->count();
    }
}
