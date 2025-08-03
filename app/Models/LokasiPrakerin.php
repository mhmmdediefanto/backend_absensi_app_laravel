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
        return $this->hasMany(Siswa::class, 'lokasi_prakerin_id', 'id');
    }

    public function jadwalPrakerin()
    {
        return $this->hasMany(JadwalPrakerin::class, 'lokasi_prakerin_id', 'id');
    }

    // relasi dengan guru
    public function guruPembimbing()
    {
        return $this->belongsToMany(Guru::class, 'guru_lokasis', 'lokasi_prakerin_id', 'guru_id');
    }

    public function syncJadwal($jadwalBaru)
    {
        // Hapus semua jadwal lama
        $this->jadwalPrakerin()->delete();

        // Tambahkan jadwal baru
        foreach ($jadwalBaru as $jadwal) {
            $this->jadwalPrakerin()->create([
                'hari' => $jadwal['hari'],
                'jam_masuk' => $jadwal['jam_masuk'],
                'jam_pulang' => $jadwal['jam_pulang'],
                'toleransi_masuk' => $jadwal['toleransi_masuk'] ?? 10,
                'toleransi_pulang' => $jadwal['toleransi_pulang'] ?? 0,
                'status_aktif' => $jadwal['status_aktif'] ?? true,
                'keterangan' => $jadwal['keterangan'] ?? 'Jadwal input',
            ]);
        }
    }
}
