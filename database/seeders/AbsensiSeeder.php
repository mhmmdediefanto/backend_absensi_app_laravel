<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        $userId = 3; // ganti sesuai user yang mau diisi
        $lokasiId = 1;

        // Ambil daftar tanggal unik untuk sebulan terakhir
        $tanggalList = [];
        for ($i = 0; $i < 15; $i++) {
            $tanggalList[] = $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d');
        }
        $tanggalList = array_unique($tanggalList); // pastikan tidak ada tanggal duplikat

        foreach ($tanggalList as $tanggal) {
            // Absen masuk
            DB::table('absensis')->insert([
                'user_id' => $userId,
                'lokasi_prakerin_id' => $lokasiId,
                'tanggal' => $tanggal,
                'time' => $faker->time('H:i:s'),
                'tipe_absen' => 'masuk',
                'latitude' => $faker->latitude(-6.3, -6.1),
                'longitude' => $faker->longitude(106.7, 106.9),
                'status_lokasi' => $faker->randomElement(['valid', 'invalid']),
                'status_wajah' => $faker->randomElement(['valid', 'invalid']),
                'status_keterlambatan' => $faker->randomElement(['tepat_waktu', 'terlambat']),
                'foto' => 'foto_' . $faker->numberBetween(1, 10) . '.jpg',
                'keterangan' => $faker->randomElement(['Hadir', 'Alpa', 'Sakit', 'Izin']),
            ]);

            // Absen pulang
            DB::table('absensis')->insert([
                'user_id' => $userId,
                'lokasi_prakerin_id' => $lokasiId,
                'tanggal' => $tanggal,
                'time' => $faker->time('H:i:s'),
                'tipe_absen' => 'pulang',
                'latitude' => $faker->latitude(-6.3, -6.1),
                'longitude' => $faker->longitude(106.7, 106.9),
                'status_lokasi' => $faker->randomElement(['valid', 'invalid']),
                'status_wajah' => $faker->randomElement(['valid', 'invalid']),
                'status_keterlambatan' => $faker->randomElement(['tepat_waktu', 'terlambat']),
                'foto' => 'foto_' . $faker->numberBetween(1, 10) . '.jpg',
                'keterangan' => $faker->randomElement(['Hadir', 'Alpa', 'Sakit', 'Izin']),
            ]);
        }

        $this->command->info('Absensi seeder selesai.');
    }
}
