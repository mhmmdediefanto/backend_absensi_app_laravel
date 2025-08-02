<?php

namespace App\Console\Commands;

use App\Models\JadwalPrakerin;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DebugJadwalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:jadwal {lokasi_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug jadwal prakerin untuk troubleshooting';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lokasiId = $this->argument('lokasi_id');
        $hariIni = now()->locale('id')->isoFormat('dddd');
        $sekarang = now();

        $this->info("=== DEBUG JADWAL PRAKERIN ===");
        $this->info("Hari ini: " . $hariIni);
        $this->info("Waktu sekarang: " . $sekarang->format('H:i A'));
        $this->info("");

        // Query jadwal
        $query = JadwalPrakerin::query();
        if ($lokasiId) {
            $query->where('lokasi_prakerin_id', $lokasiId);
        }
        
        $jadwals = $query->get();

        if ($jadwals->isEmpty()) {
            $this->error("Tidak ada jadwal prakerin ditemukan!");
            return;
        }

        $this->info("=== DAFTAR JADWAL ===");
        foreach ($jadwals as $jadwal) {
            $this->info("ID: " . $jadwal->id);
            $this->info("Lokasi ID: " . $jadwal->lokasi_prakerin_id);
            $this->info("Hari: " . $jadwal->hari);
            $this->info("Jam Masuk: " . $jadwal->jam_masuk);
            $this->info("Jam Pulang: " . $jadwal->jam_pulang);
            $this->info("Toleransi Masuk: " . $jadwal->toleransi_masuk . " menit");
            $this->info("Toleransi Pulang: " . $jadwal->toleransi_pulang . " menit");
            $this->info("Status: " . ($jadwal->status_aktif ? "AKTIF" : "NONAKTIF"));
            $this->info("---");
        }

        // Cek jadwal hari ini
        $jadwalHariIni = JadwalPrakerin::where('hari', $hariIni)
            ->where('status_aktif', true);
        
        if ($lokasiId) {
            $jadwalHariIni->where('lokasi_prakerin_id', $lokasiId);
        }
        
        $jadwalHariIni = $jadwalHariIni->first();

        $this->info("=== JADWAL HARI INI ===");
        if (!$jadwalHariIni) {
            $this->error("Tidak ada jadwal aktif untuk hari " . $hariIni);
            return;
        }

        $jamMasuk = Carbon::createFromTimeString($jadwalHariIni->jam_masuk);
        $jamPulang = Carbon::createFromTimeString($jadwalHariIni->jam_pulang);
        $waktuMulaiAbsen = $jamMasuk->copy()->subMinutes($jadwalHariIni->toleransi_masuk);
        $batasToleransi = $jamMasuk->copy()->addMinutes($jadwalHariIni->toleransi_masuk);

        $this->info("Lokasi ID: " . $jadwalHariIni->lokasi_prakerin_id);
        $this->info("Jam Masuk: " . $jamMasuk->format('H:i A'));
        $this->info("Jam Pulang: " . $jamPulang->format('H:i A'));
        $this->info("Toleransi Masuk: " . $jadwalHariIni->toleransi_masuk . " menit");
        $this->info("Waktu Mulai Absen: " . $waktuMulaiAbsen->format('H:i A'));
        $this->info("Batas Toleransi: " . $batasToleransi->format('H:i A'));
        $this->info("");
        
        $this->info("=== STATUS ABSENSI ===");
        $this->info("Bisa absen masuk: " . ($sekarang->gte($waktuMulaiAbsen) ? "YA" : "TIDAK"));
        $this->info("Terlambat: " . ($sekarang->gt($batasToleransi) ? "YA" : "TIDAK"));
        
        // Cek batas akhir absen masuk
        $batasAkhirAbsenMasuk = $jamPulang->copy()->addMinutes($jadwalHariIni->toleransi_pulang);
        $this->info("Batas akhir absen masuk: " . $batasAkhirAbsenMasuk->format('H:i A'));
        $this->info("Sudah melebihi jam pulang: " . ($sekarang->gt($batasAkhirAbsenMasuk) ? "YA" : "TIDAK"));
        
        if ($sekarang->lt($waktuMulaiAbsen)) {
            $this->warn("Alasan tidak bisa absen: Belum waktunya absen masuk");
        } elseif ($sekarang->gt($batasAkhirAbsenMasuk)) {
            $this->warn("Alasan tidak bisa absen: Sudah melebihi jam pulang");
        } else {
            $this->info("Status: Bisa melakukan absensi");
        }

        // Cek status kehadiran
        $this->info("");
        $this->info("=== STATUS KEHADIRAN ===");
        
        $absensiHariIni = \App\Models\Absensis::where('user_id', 1) // Ganti dengan user yang sedang login
            ->where('tanggal', now()->toDateString())
            ->get();

        $sudahAbsenMasuk = $absensiHariIni->where('tipe_absen', 'masuk')->count() > 0;
        $sudahAbsenPulang = $absensiHariIni->where('tipe_absen', 'pulang')->count() > 0;

        if ($sekarang->gt($batasAkhirAbsenMasuk) && !$sudahAbsenMasuk) {
            $this->error("Status: ALPA/TIDAK HADIR");
            $this->warn("Alasan: Belum absen masuk hingga batas waktu");
        } elseif ($sudahAbsenMasuk) {
            $absensiMasuk = $absensiHariIni->where('tipe_absen', 'masuk')->first();
            if ($absensiMasuk) {
                $waktuAbsenMasuk = \Carbon\Carbon::createFromTimeString($absensiMasuk->time);
                if ($waktuAbsenMasuk->gt($batasToleransi)) {
                    $this->warn("Status: HADIR TERLAMBAT");
                    $this->info("Waktu absen: " . $waktuAbsenMasuk->format('H:i A'));
                } else {
                    $this->info("Status: HADIR TEPAT WAKTU");
                    $this->info("Waktu absen: " . $waktuAbsenMasuk->format('H:i A'));
                }
            }
        } elseif ($sekarang->gte($waktuMulaiAbsen) && $sekarang->lte($batasAkhirAbsenMasuk)) {
            $this->warn("Status: BELUM ABSEN");
            $this->info("Alasan: Masih dalam waktu absen");
        } else {
            $this->warn("Status: BELUM WAKTUNYA");
            $this->info("Alasan: Belum waktunya absen");
        }
    }
} 