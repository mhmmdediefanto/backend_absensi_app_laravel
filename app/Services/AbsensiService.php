<?php

namespace App\Services;

use App\Models\Absensis;
use App\Models\JadwalPrakerin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AbsensiService
{

    public function absensi(array $data)
    {
        return Absensis::create($data);
    }

    public function checkAbsensi(string $tipe, int $lokasi_prakerin_id, bool $bypassTime = false)
    {
        // cek hari ini
        $hariIni = now()->locale('id')->isoFormat('dddd');

        // cek jadwal prakerin
        $jadwal = JadwalPrakerin::where('lokasi_prakerin_id', $lokasi_prakerin_id)
            ->where('hari', $hariIni)
            ->where('status_aktif', true)
            ->first();

        // jika tidak ada jadwal prakerin
        if (!$jadwal) {
            return ['status' => false, 'message' => 'Hari ini (' . $hariIni . ') tidak ada jadwal prakerin yang aktif. Silakan hubungi admin untuk informasi lebih lanjut.'];
        }

        // cek waktu
        $sekarang = now();
        $jamMasuk = Carbon::createFromTimeString($jadwal->jam_masuk);
        $jamPulang = Carbon::createFromTimeString($jadwal->jam_pulang);


        // cek absensi hari ini
        $absensiHariIni = Absensis::where('user_id', Auth::id())
            ->where('tanggal', now()->toDateString())
            ->get();

        $sudahAbsenMasuk = $absensiHariIni->where('tipe_absen', 'masuk')->count() > 0;
        $sudahAbsenPulang = $absensiHariIni->where('tipe_absen', 'pulang')->count() > 0;

        // Validasi berdasarkan tipe absen
        if ($tipe === 'masuk') {
            // Cek apakah sudah absen masuk hari ini
            if ($sudahAbsenMasuk) {
                return ['status' => false, 'message' => 'Anda sudah melakukan absensi masuk hari ini. Tidak dapat melakukan absensi masuk ganda.'];
            }

            // Skip validasi waktu jika bypass
            if (!$bypassTime) {
                // Cek apakah sudah melebihi jam pulang
                $batasAkhirAbsenMasuk = $jamPulang->copy()->addMinutes($jadwal->toleransi_pulang);
                if ($sekarang->gt($batasAkhirAbsenMasuk)) {
                    return ['status' => false, 'message' => 'Tidak dapat absen masuk karena sudah melebihi jam pulang (' . $jamPulang->format('H:i A') . '). Silakan hubungi admin jika ada keperluan khusus.'];
                }

                // Cek apakah belum waktunya absen masuk (bisa absen sebelum jam masuk dengan toleransi)
                $waktuMulaiAbsen = $jamMasuk->copy()->subMinutes($jadwal->toleransi_masuk);



                // Jika sekarang masih sebelum waktu mulai absen
                if ($sekarang->lt($waktuMulaiAbsen)) {
                    return ['status' => false, 'message' => "Belum waktunya absen masuk. Absensi masuk dapat dilakukan mulai " . $waktuMulaiAbsen->format('H:i A') . " (Jam masuk: " . $jamMasuk->format('H:i A') . ")"];
                }
            }

            // Cek keterlambatan (terlambat jika absen setelah jam masuk + toleransi)
            $batasToleransi = $jamMasuk->copy()->addMinutes($jadwal->toleransi_masuk);
            $terlambat = $sekarang->gt($batasToleransi);

            return ['status' => true, 'terlambat' => $terlambat];
        } elseif ($tipe === 'pulang') {
            // Cek apakah sudah absen pulang hari ini
            if ($sudahAbsenPulang) {
                return ['status' => false, 'message' => 'Anda sudah melakukan absensi pulang hari ini. Tidak dapat melakukan absensi pulang ganda.'];
            }

            // Cek apakah sudah absen masuk hari ini
            if (!$sudahAbsenMasuk) {
                return ['status' => false, 'message' => 'Anda belum melakukan absensi masuk hari ini. Silakan absen masuk terlebih dahulu sebelum absen pulang.'];
            }

            // Skip validasi waktu jika bypass
            if (!$bypassTime) {
                // Cek apakah belum waktunya absen pulang (bisa absen sebelum jam pulang dengan toleransi)
                $waktuMulaiAbsenPulang = $jamPulang->copy()->subMinutes($jadwal->toleransi_pulang);
                if ($sekarang->lt($waktuMulaiAbsenPulang)) {
                    return ['status' => false, 'message' => 'Belum waktunya absen pulang. Absensi pulang dapat dilakukan mulai ' . $waktuMulaiAbsenPulang->format('H:i A') . ' (Jam pulang: ' . $jamPulang->format('H:i A') . ')'];
                }
            }

            return ['status' => true, 'terlambat' => false];
        }

        return ['status' => false, 'message' => 'Tipe absen tidak valid.'];
    }
}
