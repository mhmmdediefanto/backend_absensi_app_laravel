<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class KegiatanPrakerin extends Model
{
    protected $table = 'kegiatan_prakerins';
    protected $fillable = ['siswa_id', 'tanggal', 'catatan_guru', 'deskripsi', 'judul'];

    protected static function booted()
    {
        static::saved(function () {
            Cache::flush();
        });

        static::deleted(function () {
            Cache::flush();
        });
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
