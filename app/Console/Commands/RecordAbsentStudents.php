<?php

namespace App\Console\Commands;

use App\Models\Absensis;
use App\Models\JadwalPrakerin;
use App\Models\Siswa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RecordAbsentStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'record:absent-students {--force : Force record even if already recorded}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mencatat siswa yang alpa/tidak hadir hari ini';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hariIni = now()->locale('id')->isoFormat('dddd');
        $tanggalHariIni = now()->toDateString();
        $force = $this->option('force');

        $this->info("=== MENCATAT SISWA YANG ALPA ===");
        $this->info("Hari ini: {$hariIni}");
        $this->info("Tanggal: {$tanggalHariIni}");
        $this->info("");

        // Ambil semua jadwal aktif hari ini
        $jadwalsHariIni = JadwalPrakerin::where('hari', $hariIni)
            ->where('status_aktif', true)
            ->get();

        if ($jadwalsHariIni->isEmpty()) {
            $this->warn("Tidak ada jadwal aktif untuk hari {$hariIni}");
            return;
        }

        $totalAlpa = 0;

        foreach ($jadwalsHariIni as $jadwal) {
            $this->info("Memeriksa lokasi prakerin ID: {$jadwal->lokasi_prakerin_id}");
            
            // Ambil semua siswa yang terdaftar di lokasi prakerin ini
            $siswaLokasi = Siswa::whereHas('lokasiPrakerin', function($query) use ($jadwal) {
                $query->where('id', $jadwal->lokasi_prakerin_id);
            })->get();

            if ($siswaLokasi->isEmpty()) {
                $this->warn("Tidak ada siswa terdaftar di lokasi ini");
                continue;
            }

            $this->info("Total siswa di lokasi ini: {$siswaLokasi->count()}");
            $alpaLokasi = 0;

            foreach ($siswaLokasi as $siswa) {
                // Cek apakah sudah ada record alpa hari ini
                $sudahRecordAlpa = Absensis::where('user_id', $siswa->user_id)
                    ->where('tanggal', $tanggalHariIni)
                    ->where('keterangan', 'Alpa')
                    ->exists();

                if ($sudahRecordAlpa && !$force) {
                    $this->line("✓ {$siswa->nama} ({$siswa->nis}) - Sudah dicatat alpa");
                    continue;
                }

                // Cek apakah siswa sudah absen hari ini
                $sudahAbsen = Absensis::where('user_id', $siswa->user_id)
                    ->where('tanggal', $tanggalHariIni)
                    ->where('tipe_absen', 'masuk')
                    ->where('keterangan', '!=', 'Alpa')
                    ->exists();

                if ($sudahAbsen) {
                    $this->line("✓ {$siswa->nama} ({$siswa->nis}) - Sudah absen");
                    continue;
                }

                // Jika belum absen, catat sebagai alpa
                Absensis::create([
                    'user_id' => $siswa->user_id,
                    'lokasi_prakerin_id' => $jadwal->lokasi_prakerin_id,
                    'tanggal' => $tanggalHariIni,
                    'time' => '00:00:00',
                    'tipe_absen' => 'masuk',
                    'latitude' => 0,
                    'longitude' => 0,
                    'status_lokasi' => 'invalid',
                    'status_wajah' => 'invalid',
                    'status_keterlambatan' => 'terlambat',
                    'keterangan' => 'Alpa',
                    'foto' => null
                ]);

                $this->error("✗ {$siswa->nama} ({$siswa->nis}) - DICATAT ALPA");
                $alpaLokasi++;
                $totalAlpa++;

                Log::info("Siswa {$siswa->nama} ({$siswa->nis}) dicatat alpa di lokasi {$jadwal->lokasi_prakerin_id}");
            }

            $this->info("Total alpa di lokasi ini: {$alpaLokasi}");
            $this->info("---");
        }

        $this->info("");
        $this->info("=== HASIL AKHIR ===");
        $this->info("Total siswa yang dicatat alpa: {$totalAlpa}");
        
        if ($totalAlpa > 0) {
            $this->error("Ada {$totalAlpa} siswa yang tidak hadir hari ini!");
        } else {
            $this->info("Semua siswa sudah hadir atau sudah dicatat alpa!");
        }
    }
}
