<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\KegiatanPrakerin;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class KegiatanPrakerinController extends Controller
{
    public $userId;

    public function __construct()
    {
        //ambil user id prakerin login
        $this->userId = Siswa::where('user_id', Auth::user()->id)->value('id');
    }

    public function getKegiatanPrakerin()
    {
        if (!$this->userId) {
            return response()->json([
                'message' => 'Siswa tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        $cacheKey = 'kegiatan_prakerin_' . $this->userId;
        $kegiatanPrakerin = Cache::remember($cacheKey, 60, function () {
            return KegiatanPrakerin::where('siswa_id', $this->userId)
                ->latest()
                ->paginate(10);
        });

        return response()->json([
            'message' => 'Kegiatan prakerin berhasil diambil',
            'status' => 'success',
            'data' => $kegiatanPrakerin
        ]);
    }


    public function createKegiatanPrakerin(Request $request)
    {

        try {
            $validatedData = $request->validate([
                'deskripsi' => 'required',
                'judul' => 'required',
            ]);

            $kegiatanPrakerin = KegiatanPrakerin::create([
                'siswa_id' => $this->userId,
                'deskripsi' => $validatedData['deskripsi'],
                'judul' => $validatedData['judul'],
                'tanggal' => now()->format('Y-m-d'),
            ]);

            if ($kegiatanPrakerin) {
                return response()->json([
                    'message' => 'Kegiatan prakerin berhasil dibuat',
                    'status' => 'success',
                    'data' => $kegiatanPrakerin
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Kegiatan prakerin gagal dibuat',
                'status' => 'error',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function destroyKegiatanPrakerin($id)
    {
        if (!$this->userId) {
            return response()->json([
                'message' => 'Siswa tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        $kegiatanPrakerin = KegiatanPrakerin::find($id);
        if (!$kegiatanPrakerin) {
            return response()->json([
                'message' => 'Kegiatan prakerin tidak ditemukan',
                'status' => 'error'
            ], 404);
        }

        $kegiatanPrakerin->delete();

        if ($kegiatanPrakerin) {
            return response()->json([
                'message' => 'Kegiatan prakerin berhasil dihapus',
                'status' => 'success'
            ]);
        }
        return response()->json([
            'message' => 'Kegiatan prakerin gagal dihapus',
            'status' => 'error'
        ], 400);
    }
}
