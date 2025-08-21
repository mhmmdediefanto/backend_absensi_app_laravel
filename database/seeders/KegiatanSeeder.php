<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();

        // Ambil semua siswa dari tabel 'siswas'
        $siswaIds = DB::table('siswas')->pluck('id')->toArray();

        foreach ($siswaIds as $siswa_id) {
            // Generate 5 hari kegiatan berbeda per siswa
            for ($i = 0; $i < 20; $i++) {
                $tanggal = Carbon::now()->subDays(rand(0, 30))->format('Y-m-d');

                // Pastikan kombinasi siswa_id + tanggal unik
                if (!DB::table('kegiatan_prakerins')->where('siswa_id', $siswa_id)->where('tanggal', $tanggal)->exists()) {
                    DB::table('kegiatan_prakerins')->insert([
                        'siswa_id' => $siswa_id,
                        'judul' => $faker->sentence(3),
                        'deskripsi' => $faker->paragraph(),
                        'tanggal' => $tanggal,
                        'catatan_guru' => $faker->optional()->sentence(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Kegiatan seeder selesai.');
    }
}
