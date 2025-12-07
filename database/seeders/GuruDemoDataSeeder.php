<?php

namespace Database\Seeders;

use App\Models\Absensis;
use App\Models\Guru;
use App\Models\GuruLokasi;
use App\Models\LokasiPrakerin;
use App\Models\PembimbingSiswa;
use App\Models\Siswa;
use App\Models\KegiatanPrakerin;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GuruDemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gurus = Guru::with('user')->get();
        $students = Siswa::with('user')->get();
        $locations = LokasiPrakerin::all();

        if ($gurus->isEmpty() || $students->isEmpty() || $locations->isEmpty()) {
            $this->command?->warn('GuruDemoDataSeeder: Data guru, siswa, atau lokasi belum tersedia. Seeder dilewati.');
            return;
        }

        Schema::disableForeignKeyConstraints();
        DB::table('pembimbing_siswas')->truncate();
        DB::table('guru_lokasis')->truncate();
        DB::table('absensis')->truncate();
        DB::table('kegiatan_prakerins')->truncate();
        Schema::enableForeignKeyConstraints();

        $faker = Faker::create('id_ID');
        $dates = collect(range(0, 13))->map(fn ($i) => Carbon::today()->subDays($i));

        foreach ($students as $index => $student) {
            $guru = $gurus[$index % $gurus->count()];
            $location = $locations[$index % $locations->count()];

            // Pastikan relasi guru - lokasi tercatat
            GuruLokasi::firstOrCreate(
                [
                    'guru_id' => $guru->id,
                    'lokasi_prakerin_id' => $location->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Relasi pembimbing - siswa
            PembimbingSiswa::updateOrCreate(
                [
                    'guru_id' => $guru->id,
                    'siswa_id' => $student->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Update informasi siswa
            $student->update([
                'lokasi_prakerin_id' => $location->id,
                'status_prakerin' => 'aktif',
            ]);

            // Generate data absensi 14 hari terakhir
            foreach ($dates as $date) {
                $status = Arr::random([
                    'Hadir',
                    'Hadir',
                    'Hadir',
                    'Hadir',
                    'Izin',
                    'Sakit',
                    'Alpa',
                ]);

                $baseLat = $location->latitude ?? $faker->latitude(-6.5, -6.0);
                $baseLng = $location->longitude ?? $faker->longitude(106.6, 107.0);

                $latitude = $baseLat + $faker->randomFloat(6, -0.0008, 0.0008);
                $longitude = $baseLng + $faker->randomFloat(6, -0.0008, 0.0008);

                if ($status === 'Alpa') {
                    Absensis::create([
                        'user_id' => $student->user_id,
                        'lokasi_prakerin_id' => $location->id,
                        'tanggal' => $date->toDateString(),
                        'time' => null,
                        'tipe_absen' => 'masuk',
                        'latitude' => null,
                        'longitude' => null,
                        'status_lokasi' => null,
                        'status_wajah' => null,
                        'status_keterlambatan' => null,
                        'foto' => null,
                        'keterangan' => 'Alpa',
                    ]);

                    continue;
                }

                $masukTime = Carbon::createFromTime(8, 0)->addMinutes($faker->numberBetween(0, 45));
                $statusKeterlambatan = $masukTime->greaterThan(Carbon::createFromTime(8, 15))
                    ? 'terlambat'
                    : 'tepat_waktu';

                Absensis::create([
                    'user_id' => $student->user_id,
                    'lokasi_prakerin_id' => $location->id,
                    'tanggal' => $date->toDateString(),
                    'time' => $masukTime->format('H:i:s'),
                    'tipe_absen' => 'masuk',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'status_lokasi' => 'valid',
                    'status_wajah' => 'valid',
                    'status_keterlambatan' => $statusKeterlambatan,
                    'foto' => null,
                    'keterangan' => $status,
                ]);

                if ($status === 'Hadir') {
                    $pulangTime = Carbon::createFromTime(16, 0)->subMinutes($faker->numberBetween(0, 30));

                    Absensis::create([
                        'user_id' => $student->user_id,
                        'lokasi_prakerin_id' => $location->id,
                        'tanggal' => $date->toDateString(),
                        'time' => $pulangTime->format('H:i:s'),
                        'tipe_absen' => 'pulang',
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'status_lokasi' => 'valid',
                        'status_wajah' => 'valid',
                        'status_keterlambatan' => null,
                        'foto' => null,
                        'keterangan' => 'Hadir',
                    ]);
                }

                KegiatanPrakerin::create([
                    'siswa_id' => $student->id,
                    'tanggal' => $date->toDateString(),
                    'judul' => $faker->sentence(6),
                    'deskripsi' => '<p>' . implode('</p><p>', $faker->paragraphs(2)) . '</p>',
                    'catatan_guru' => null,
                ]);
            }
        }

        $this->command?->info('GuruDemoDataSeeder: data relasi guru, siswa, dan absensi berhasil dibuat.');
    }
}


