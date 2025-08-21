<?php

namespace Database\Seeders;

use App\Models\Siswa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LokasiPrakerinSiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siswas = Siswa::all();
        foreach ($siswas as $siswa) {
            $siswa->lokasi_prakerin_id = rand(1, 10);
            $siswa->save();
        }

        $this->command->info('Lokasi prakerin siswa berhasil diisi');
    }
}
