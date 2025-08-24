<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\GuruLokasi as ModelsGuruLokasi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class kelolaPembimngLokasiController extends Controller
{
    public function getKelolaPembimbingLokasi(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
        ]);

        $search = $request->input('search');

        $kelolaPembimbingLokasi = ModelsGuruLokasi::with([
            'guru' => function ($query) {
                $query->select('id', 'nama');
            },
            'lokasiPrakerin' => function ($query) {
                $query->select('id', 'nama_instansi');
            }
        ])
            ->select('id', 'guru_id', 'lokasi_prakerin_id')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('guru', function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%");
                })
                    ->orWhereHas('lokasiPrakerin', function ($q) use ($search) {
                        $q->where('nama_instansi', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10);

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
            'lokasi_prakerin_id' => 'required|array',
            'lokasi_prakerin_id.*' => 'exists:lokasi_prakerins,id',
        ]);

        // Cegah duplikat relasi
        $exists = DB::table('guru_lokasis')
            ->where('guru_id', $request->guru_id)
            ->whereIn('lokasi_prakerin_id', $request->lokasi_prakerin_id)
            ->exists();


        if ($exists) {
            throw ValidationException::withMessages([
                'lokasi_prakerin_id' => ['Guru ini sudah terdaftar di lokasi yang dipilih.'],
            ]);
        }

        $guru = Guru::find($request->guru_id);
        $guru->lokasiPrakerins()->syncWithoutDetaching($request->lokasi_prakerin_id);

        // tambahkan guru pembimbing ke table pivot pembimbing siswas
        $siswa = Siswa::whereIn('lokasi_prakerin_id', $request->lokasi_prakerin_id)->get();
        if ($siswa->count() > 0) {
            foreach ($siswa as $s) {
                $s->guruPembimbingLokasi()->syncWithoutDetaching($request->guru_id);
            }
        }

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
