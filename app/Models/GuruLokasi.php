<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Guru;
use App\Models\LokasiPrakerin;

class GuruLokasi extends Model
{
    protected $table = 'guru_lokasis';
    protected $fillable = ['guru_id', 'lokasi_prakerin_id'];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }
    
    public function lokasiPrakerin()
    {
        return $this->belongsTo(LokasiPrakerin::class, 'lokasi_prakerin_id');
    }
}
