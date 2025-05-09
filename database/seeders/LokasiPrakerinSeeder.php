<?php

namespace Database\Seeders;

use App\Models\LokasiPrakerin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LokasiPrakerinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama_instansi' => 'PT Maju Bersama',
                'alamat' => 'Jl. Melati No. 12, Jakarta',
                'latitude' => -6.200123,
                'longitude' => 106.822456,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_instansi' => 'CV Sukses Selalu',
                'alamat' => 'Jl. Mawar No. 5, Bandung',
                'latitude' => -6.914744,
                'longitude' => 107.609810,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_instansi' => 'UD Teknologi Hebat',
                'alamat' => 'Jl. Kenanga No. 7, Surabaya',
                'latitude' => -7.257472,
                'longitude' => 112.752090,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_instansi' => 'PT Inovasi Nusantara',
                'alamat' => 'Jl. Cempaka No. 18, Yogyakarta',
                'latitude' => -7.795580,
                'longitude' => 110.369490,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_instansi' => 'CV Solusi Digital',
                'alamat' => 'Jl. Flamboyan No. 2, Semarang',
                'latitude' => -6.966667,
                'longitude' => 110.416664,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_instansi' => 'PT Kreatif Mandiri',
                'alamat' => 'Jl. Anggrek No. 9, Malang',
                'latitude' => -7.981894,
                'longitude' => 112.626503,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_instansi' => 'CV Jaya Abadi',
                'alamat' => 'Jl. Dahlia No. 15, Solo',
                'latitude' => -7.569673,
                'longitude' => 110.825134,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_instansi' => 'PT Mitra Teknologi',
                'alamat' => 'Jl. Merpati No. 3, Bogor',
                'latitude' => -6.597147,
                'longitude' => 106.806038,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_instansi' => 'UD Digital Kreatif',
                'alamat' => 'Jl. Rajawali No. 8, Bekasi',
                'latitude' => -6.238270,
                'longitude' => 106.975571,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_instansi' => 'CV Media Solusi',
                'alamat' => 'Jl. Elang No. 20, Kudus',
                'latitude' => -6.805860,
                'longitude' => 110.841423,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $row) {
            LokasiPrakerin::create($row);
        }
    }
}
