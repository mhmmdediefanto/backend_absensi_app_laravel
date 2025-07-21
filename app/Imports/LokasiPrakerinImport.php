<?php

namespace App\Imports;

use App\Models\JadwalPrakerin;
use App\Models\LokasiPrakerin;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LokasiPrakerinImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Cari atau buat lokasi
        $lokasi = LokasiPrakerin::firstOrCreate(
            ['nama_instansi' => $row['nama_instansi']],
            [
                'alamat'            => $row['alamat'],
                'latitude'          => $row['latitude'],
                'longitude'         => $row['longitude'],
                'kontak_person'     => $row['kontak_person'],
                'no_telp'           => $row['no_telp'],
                'email'             => $row['email'],
                'website'           => $row['website'],
                'bidang_usaha'      => $row['bidang_usaha'],
                'kapasitas_siswa'   => $row['kapasitas_siswa'],
                'status_kerjasama'  => $row['status_kerjasama'],
                'mulai_kerjasama'   => $row['mulai_kerjasama'],
                'radius_absen'      => $row['radius_absen'],
                'foto_instansi'     => $row['foto_instansi'],
                'status_aktif'      => $row['status_aktif'],
                'deskripsi'         => $row['deskripsi'],
                'jenis_instansi'    => $row['jenis_instansi'],
            ]
        );

        // Tambahkan jadwal absensi per baris
        JadwalPrakerin::create([
            'lokasi_prakerin_id' => $lokasi->id,
            'hari'               => $row['hari'],
            'jam_masuk'          => $row['jam_masuk'],
            'jam_pulang'         => $row['jam_pulang'],
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
