<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembimbingSiswa extends Model
{
    protected $table = 'pembimbing_siswas';
    protected $fillable = ['siswa_id', 'guru_id'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id', 'id');
    }
}
