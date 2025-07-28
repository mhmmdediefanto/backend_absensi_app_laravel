<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\LokasiPrakerin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LokasiPembimbingPrakerin extends Controller
{
    public function getAllLokasiPembimbing(Request $request)
    {
        $guru = Guru::select('id', 'nama')->find(Auth::user()->id);

        $lokasi = $guru->lokasiPrakerins()
            ->when($request->search, function ($query, $search) {
                $query->where('nama_instansi', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            })
            ->paginate(10);

        return response()->json([
            'guru' => $guru,
            'lokasi' => $lokasi,
            'total_all' => $lokasi->total(),
            'status' => 'success',
            'message' => 'Data lokasi pembimbing prakerin berhasil diambil'
        ], 200);
    }

    public function getDetailLokasiPembimbing($id)
    {
        $lokasi = LokasiPrakerin::with([
            'siswa' => function ($query) {
                $query->select('id', 'nama', 'nis', 'kelas', 'lokasi_prakerin_id', 'status_prakerin'); // pastikan FK disertakan
            },
            'jadwalPrakerin',
            'guruPembimbing' => function ($query) {
                $query->select('guru_id', 'nama', 'nip', 'no_hp', 'jabatan', 'lokasi_prakerin_id'); // FK juga disertakan
            }
        ])->find($id);

        if (!$lokasi) {
            return response()->json([
                'message' => 'Lokasi prakerin tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'lokasi' => $lokasi,
            'status' => 'success',
            'message' => 'Data lokasi pembimbing prakerin berhasil diambil'
        ], 200);
    }
}
