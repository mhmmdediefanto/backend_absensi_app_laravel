<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuruLokasi;
use App\Models\Siswa;
use Illuminate\Http\Request;

class PenempatanSiswaController extends Controller
{

    public function index()
    {
        $siswa = Siswa::with(['lokasiPrakerin', 'guruPembimbingLokasi'])->whereNotNull('lokasi_prakerin_id')->latest()->paginate(10);
        return response()->json([
            'message' => 'Data siswa berhasil diambil',
            'total_siswa_lokasi' => Siswa::countSiswaLokasi(),
            'code' => 200,
            'data' => $siswa,
        ], 200);
    }

    public function addPenempatan(Request $request)
    {
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'lokasi_prakerin_id' => 'required|exists:lokasi_prakerins,id',
        ]);

        // cek apakah siswa sudah memiliki penempatan
        $cekSiswa = Siswa::where('id', $validatedData['siswa_id'])->first();
        if (!$cekSiswa) {
            return response()->json([
                'message' => 'Siswa tidak ditemukan',
                'code' => 404
            ], 404);
        }

        // Cek apakah siswa sudah memiliki penempatan
        if (!is_null($cekSiswa->lokasi_prakerin_id)) {
            return response()->json([
                'message' => 'Siswa sudah memiliki penempatan',
                'code' => 422
            ], 422);
        }

        // Update lokasi prakerin siswa
        $cekSiswa->lokasi_prakerin_id = $validatedData['lokasi_prakerin_id'];
        $cekSiswa->status_prakerin = 'aktif';
        $cekSiswa->save();

        // cek apakah lokasi tersebut sudah memiliki guru pembimbing
        $guruId = GuruLokasi::where('lokasi_prakerin_id', $validatedData['lokasi_prakerin_id'])->pluck('guru_id');

        // tambahkan guru pembimbing ke table pivot pembimbing siswas
        $cekSiswa->guruPembimbingLokasi()->syncWithoutDetaching($guruId);

        return response()->json([
            'message' => 'Penempatan siswa berhasil ditambahkan',
            'code' => 200
        ], 200);
    }

    public function deletePenempatan($id)
    {
        // cek apakah ada siswa dengan id tersebut dan memiliki penempatan
        $siswa = Siswa::where('id', $id)->whereNotNull('lokasi_prakerin_id')->first();
        if (!$siswa) {
            return response()->json([
                'message' => 'Siswa tidak ditemukan',
                'code' => 404
            ], 404);
        }

        // hapus penempatan siswa
        $siswa->lokasi_prakerin_id = null;
        $siswa->status_prakerin = 'nonaktif';
        $siswa->save();

        // hapus guru pembimbing dari table pivot pembimbing siswas
        $siswa->guruPembimbingLokasi()->detach();

        return response()->json([
            'message' => 'Penempatan siswa berhasil dihapus',
            'code' => 200
        ], 200);
    }

    public function batalkanPenempatan($id)
    {
        $siswa = Siswa::where('id', $id)->whereNotNull('lokasi_prakerin_id')->first();
        if (!$siswa) {
            return response()->json([
                'message' => 'Siswa tidak ditemukan',
                'code' => 404
            ], 404);
        }

        $siswa->status_prakerin = 'dibatalkan';
        $siswa->lokasi_prakerin_id = null;
        $siswa->save();

        return response()->json([
            'message' => 'Penempatan siswa berhasil dibatalkan',
            'code' => 200
        ], 200);
    }
}
