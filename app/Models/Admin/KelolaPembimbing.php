<?php

namespace App\Models\Admin;

use App\Models\Guru;
use App\Models\LokasiPrakerin;
use Illuminate\Database\Eloquent\Model;

class KelolaPembimbing extends Model
{
    protected $table = 'pembimbings';
    protected $fillable = ['guru_id', 'lokasi_prakerin_id', 'tahun_ajaran'];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function lokasiPrakerin()
    {
        return $this->belongsTo(LokasiPrakerin::class, 'lokasi_prakerin_id');
    }
}
