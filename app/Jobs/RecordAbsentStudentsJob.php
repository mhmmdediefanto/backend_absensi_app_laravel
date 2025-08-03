<?php

namespace App\Jobs;

use App\Models\Absensis;
use App\Models\JadwalPrakerin;
use App\Models\Siswa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecordAbsentStudentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 menit timeout
    public $tries = 3; // Retry 3 kali jika gagal

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $hariIni = now()->locale('id')->isoFormat('dddd');
        $tanggalHariIni = now()->toDateString();

        Log::info("Memulai pencatatan alpa untuk hari: {$hariIni}");

        // Ambil semua jadwal aktif hari ini
        $jadwalsHariIni = JadwalPrakerin::where('hari', $hariIni)
            ->where('status_aktif', true)
            ->get();

        if ($jadwalsHariIni->isEmpty()) {
            Log::info("Tidak ada jadwal aktif untuk hari {$hariIni}");
            return;
        }

        $totalAlpa = 0;
        $totalJadwal = $jadwalsHariIni->count();

        foreach ($jadwalsHariIni as $index => $jadwal) {
                            Log::info("Memproses jadwal " . ($index + 1) . "/{$totalJadwal} - Lokasi ID: {$jadwal->lokasi_prakerin_id}");

            try {
                // Ambil semua siswa yang terdaftar di lokasi prakerin ini
                $siswaLokasi = Siswa::whereHas('lokasiPrakerin', function($query) use ($jadwal) {
                    $query->where('id', $jadwal->lokasi_prakerin_id);
                })->get();

                if ($siswaLokasi->isEmpty()) {
                    Log::info("Tidak ada siswa terdaftar di lokasi {$jadwal->lokasi_prakerin_id}");
                    continue;
                }

                $alpaLokasi = 0;
                $totalSiswa = $siswaLokasi->count();

                foreach ($siswaLokasi as $siswaIndex => $siswa) {
                    // Cek apakah sudah ada record alpa hari ini
                    $sudahRecordAlpa = Absensis::where('user_id', $siswa->user_id)
                        ->where('tanggal', $tanggalHariIni)
                        ->where('keterangan', 'Alpa')
                        ->exists();

                    if ($sudahRecordAlpa) {
                        continue;
                    }

                    // Cek apakah siswa sudah absen hari ini
                    $sudahAbsen = Absensis::where('user_id', $siswa->user_id)
                        ->where('tanggal', $tanggalHariIni)
                        ->where('tipe_absen', 'masuk')
                        ->where('keterangan', '!=', 'Alpa')
                        ->exists();

                    if ($sudahAbsen) {
                        continue;
                    }

                    // Jika belum absen, catat sebagai alpa
                    Absensis::create([
                        'user_id' => $siswa->user_id,
                        'lokasi_prakerin_id' => $jadwal->lokasi_prakerin_id,
                        'tanggal' => $tanggalHariIni,
                        'time' => '00:00:00',
                        'tipe_absen' => null,
                        'latitude' => 0,
                        'longitude' => 0,
                        'status_lokasi' => null,
                        'status_wajah' => null,
                        'keterangan' => 'Alpa',
                        'foto' => null,
                        'status_keterlambatan' => null,
                    ]);

                    $alpaLokasi++;
                    $totalAlpa++;

                    Log::info("Siswa {$siswa->nama} ({$siswa->nis}) dicatat alpa di lokasi {$jadwal->lokasi_prakerin_id}");

                    // Progress log setiap 10 siswa
                    if (($siswaIndex + 1) % 10 === 0) {
                        Log::info("Progress: " . ($siswaIndex + 1) . "/{$totalSiswa} siswa diproses di lokasi {$jadwal->lokasi_prakerin_id}");
                    }
                }

                Log::info("Lokasi {$jadwal->lokasi_prakerin_id}: {$alpaLokasi} siswa dicatat alpa dari {$totalSiswa} siswa");

            } catch (\Exception $e) {
                Log::error("Error memproses lokasi {$jadwal->lokasi_prakerin_id}: " . $e->getMessage());
                continue; // Lanjut ke lokasi berikutnya
            }
        }

        Log::info("Selesai pencatatan alpa. Total: {$totalAlpa} siswa dicatat alpa");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Job RecordAbsentStudentsJob gagal: " . $exception->getMessage());
    }
}
