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
        $check = $this->checkAbsensi($data['tipe_absen'], $data['lokasi_prakerin_id']);

        if (!$check['status']) {
            return ['error' => $check['message']];
        }

        // keterangan jika terlambat
        $data['keterangan'] = ($check['terlambat'] ?? false) ? 'Terlambat' : 'Hadir';

        return Absensis::create($data);
    }



    private function checkAbsensi(string $tipe, int $lokasi_prakerin_id)
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
            return ['status' => false, 'message' => 'Hari ini tidak ada jadwal prakerin.'];
        }

        // cek waktu
        $sekarang = now();

        // cek waktu masuk
        $jamMasuk = Carbon::createFromTimeString($jadwal->jam_masuk)->subMinutes($jadwal->toleransi_masuk);

        // cek waktu pulang
        $jamPulang = Carbon::createFromTimeString($jadwal->jam_pulang)->addMinutes($jadwal->toleransi_pulang);

        // jika sudah melebihi jam pulang
        if ($tipe === 'masuk' && $sekarang->gt($jamPulang)) {
            return ['status' => false, 'message' => 'Sudah melebihi jam pulang.'];
        }

        // jika belum waktunya absen masuk
        if ($sekarang->lt($jamMasuk)) {
            return ['status' => false, 'message' => "Belum waktunya absen. Jam masuk mulai $jamMasuk"];
        }

        // jika belum waktunya absen pulang
        if ($tipe === 'pulang' && $sekarang->lt(Carbon::createFromTimeString($jadwal->jam_pulang))) {
            return ['status' => false, 'message' => 'Belum waktunya absen pulang.'];
        }

        // cek absensi hari ini
        $exists = Absensis::where('user_id', Auth::id())
            ->where('tanggal', now()->toDateString())
            ->where('tipe_absen', $tipe)
            ->exists();

        // jika sudah melakukan absensi hari ini
        if ($exists) {
            return ['status' => true, 'message' => "Anda sudah melakukan absensi $tipe hari ini."];
        }

        // cek keterlambatan
        if ($tipe === 'masuk') {
            $batasToleransi = Carbon::createFromTimeString($jadwal->jam_masuk)->addMinutes($jadwal->toleransi_masuk);
            if ($sekarang->gt($batasToleransi)) {
                return ['status' => true, 'terlambat' => true];
            }
        }

        return ['status' => true, 'terlambat' => false];
    }
}
