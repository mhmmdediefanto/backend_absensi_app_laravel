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
