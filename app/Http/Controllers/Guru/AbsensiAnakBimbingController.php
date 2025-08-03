<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PembimbingSiswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AbsensiAnakBimbingController extends Controller
{

    public function getAbsensiAnakBimbingToday()
    {
        $absensiSiswa = PembimbingSiswa::where('guru_id', Auth::user()->id)
            ->with(['siswa' => function ($query) {
                $query->select('id', 'user_id', 'nama', 'nis', 'kelas', 'lokasi_prakerin_id');
            }, 'siswa.lokasiPrakerin' => function ($query) {
                $query->select('id', 'nama_instansi', 'alamat');
            }, 'siswa.absensi' => function ($query) {
                $query->where('tanggal', now()->toDateString());
                $query->select('id', 'user_id', 'tanggal', 'status_keterlambatan', 'tipe_absen');
            }])->get();



        return response()->json([
            'data' => $absensiSiswa,
            'status' => 'success',
            'message' => 'Data absensi anak bimbing berhasil diambil'
        ], 200);
    }


    public function historyAbsensiAnakBimbing()
    {
        $cacheKey = 'history_absensi_anak_bimbing_' . Auth::user()->id;
        $absensiSiswa = Cache::remember($cacheKey, 60 * 6, function () {
            return PembimbingSiswa::where('guru_id', Auth::user()->id)
            ->with(['siswa' => function ($query) {
                $query->select('id', 'user_id', 'nama', 'nis', 'kelas', 'lokasi_prakerin_id');
            }, 'siswa.lokasiPrakerin' => function ($query) {
                $query->select('id', 'nama_instansi', 'alamat');
            }, 'siswa.absensi' => function ($query) {
                $query->where('tanggal', '!=', now()->toDateString());
                $query->select('id', 'user_id', 'tanggal', 'status_keterlambatan', 'tipe_absen');
            }])->get();
        });

        return response()->json([
            'data' => $absensiSiswa,
            'status' => 'success',
            'message' => 'Data absensi anak bimbing berhasil diambil'
        ], 200);
    }
}
