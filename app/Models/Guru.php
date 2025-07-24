<?php

namespace App\Models;

use App\Models\LokasiPrakerin;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $guarded = ['id'];
    protected $table = 'gurus';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function total()
    {
        return $this->count();
    }

    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'pembimbing_siswas', 'siswa_id', 'guru_id');
    }

    public function lokasiPrakerins()
    {
        return $this->belongsToMany(LokasiPrakerin::class, 'guru_lokasis', 'guru_id', 'lokasi_prakerin_id');
    }
}
