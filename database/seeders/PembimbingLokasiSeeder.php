<?php

namespace Database\Seeders;

use App\Models\GuruLokasi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PembimbingLokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contoh data guru_id dan lokasi_prakerin_id
        $data = [
            [
                'guru_id' => 1,
                'lokasi_prakerin_id' => [1, 2, 3], // guru_id 1 membimbing 3 lokasi
            ],
            [
                'guru_id' => 2,
                'lokasi_prakerin_id' => [2, 4], // guru_id 2 membimbing 2 lokasi
            ],
        ];

        foreach ($data as $item) {
            foreach ($item['lokasi_prakerin_id'] as $lokasi) {
                GuruLokasi::create([
                    'guru_id' => $item['guru_id'],
                    'lokasi_prakerin_id' => $lokasi,
                ]);
            }
        }

        $this->command->info('Data guru_lokasi berhasil diisi');
    }
}
