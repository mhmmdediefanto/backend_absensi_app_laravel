<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Absensis;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardSiswaController extends Controller
{

    public function dashboard()
    {
        $siswa = Siswa::where('user_id', Auth::user()->id)
            ->with('lokasiPrakerin', function ($query) {
                $query->select('id', 'nama_instansi', 'alamat');
            })
            ->first(['id', 'user_id', 'lokasi_prakerin_id']);
        $guruPembimbing = $siswa->lokasiPrakerin?->guruPembimbing()
            ->select('guru_id', 'nama', 'nip', 'no_hp', 'jabatan', 'lokasi_prakerin_id')
            ->get();

        $absensi = Absensis::where('user_id', Auth::user()->id);
        $statusAbsensiToday = $absensi->where('tanggal', now()->toDateString())->get(['id', 'user_id', 'tanggal', 'status_keterlambatan', 'tipe_absen', 'keterangan']);
        $rekapAbsensi = Absensis::where('user_id', Auth::id())
            ->where('tanggal', '!=', now()->toDateString())
            ->where('tipe_absen', 'masuk')
            ->selectRaw("
                    SUM(CASE WHEN keterangan = 'Hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN keterangan = 'Izin' THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN keterangan = 'Sakit' THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN keterangan = 'Alpa' THEN 1 ELSE 0 END) as alfa
                ")
            ->first();
        $jadwalHariIni = $siswa->lokasiPrakerin->jadwalPrakerin()
            ->where('hari', now()->locale('id')->isoFormat('dddd'))
            ->first(['id', 'hari', 'jam_masuk', 'jam_pulang']);
        return response()->json([
            'lokasi_prakerin' => $siswa->lokasiPrakerin,
            'guru_pembimbing' => $guruPembimbing,
            'status_absensi_today' => $statusAbsensiToday,
            'rekapAbsensi' => $rekapAbsensi,
            'jadwal_hari_ini' => $jadwalHariIni,
            'message' => 'Data berhasil diambil',
            'status' => 'success',
        ], 200);
    }
}
