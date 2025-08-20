<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegiatanPrakerin extends Model
{
    protected $table = 'kegiatan_prakerins';
    protected $fillable = ['siswa_id', 'tanggal', 'catatan_guru', 'deskripsi', 'judul'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
