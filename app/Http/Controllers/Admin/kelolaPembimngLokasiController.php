<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuruLokasi as ModelsGuruLokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class kelolaPembimngLokasiController extends Controller
{
    public function getKelolaPembimbingLokasi()
    {
        $kelolaPembimbingLokasi = ModelsGuruLokasi::with(['guru' => function ($query) {
            $query->select('id', 'nama');
        }, 'lokasiPrakerin' => function ($query) {
            $query->select('id', 'nama_instansi');
        }])->select('id', 'guru_id', 'lokasi_prakerin_id')->latest()->paginate(10);
        return response()->json([
            'message' => 'Data kelola pembimbing lokasi retrieved successfully',
            'status' => 'success',
            'data' => $kelolaPembimbingLokasi
        ]);
    }

    public function addKelola(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|exists:gurus,id',
            'lokasi_prakerin_id' => 'required|exists:lokasi_prakerins,id',
        ]);

        // Cegah duplikat relasi
        $exists = DB::table('guru_lokasis')
            ->where('guru_id', $request->guru_id)
            ->where('lokasi_prakerin_id', $request->lokasi_prakerin_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Guru sudah membimbing di lokasi ini.',
                'code' => 422
            ], 422);
        }

        // Simpan ke pivot
        DB::table('guru_lokasis')->insert([
            'guru_id' => $request->guru_id,
            'lokasi_prakerin_id' => $request->lokasi_prakerin_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Data kelola pembimbing lokasi created successfully',
            'status' => 'success',
        ], 200);
    }

    public function deleteKelola($id)
    {
        $kelolaPembimbingLokasi = ModelsGuruLokasi::find($id);
        if (!$kelolaPembimbingLokasi) {
            return response()->json([
                'message' => 'Data kelola pembimbing lokasi tidak ditemukan',
                'status' => 'error',
                'code' => 404
            ], 404);
        }
        $kelolaPembimbingLokasi->delete();
        return response()->json([
            'message' => 'Data kelola pembimbing lokasi berhasil dihapus',
            'status' => 'success',
        ], 200);
    }
}
