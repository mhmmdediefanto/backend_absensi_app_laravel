<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\KegiatanPrakerin;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class KegiatanPrakerinController extends Controller
{
    public $userId;

    public function __construct()
    {
        //ambil user id prakerin login
        $this->userId = Siswa::where('user_id', Auth::user()->id)->value('id');
    }

    public function getKegiatanPrakerin(Request $request)
    {
        if (!$this->userId) {
            return response()->json([
                'message' => 'Siswa tidak ditemukan',
                'status'  => 'error'
            ], 404);
        }

        $params = $request->all();
        $cacheKey = 'kegiatan_' . $this->userId . '_' . md5(json_encode($params));

        $kegiatanPrakerin = Cache::remember($cacheKey, 60 * 6, function () use ($request) {
            $query = KegiatanPrakerin::where('siswa_id', $this->userId);

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('judul', 'like', '%' . $request->search . '%')
                        ->orWhere('deskripsi', 'like', '%' . $request->search . '%');
                });
            }

            if ($request->filled('start_date')) {
                $query->whereDate('tanggal', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('tanggal', '<=', $request->end_date);
            }

            return $query->latest()->paginate(10);
        });


        return response()->json([
            'message' => 'Kegiatan prakerin berhasil diambil',
            'status'  => 'success',
            'data'    => $kegiatanPrakerin
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

    public function updateKegiatanPrakerin(Request $request, $id)
    {
        $validatedData = $request->validate([
            'deskripsi' => 'required',
            'judul' => 'required',
        ]);
        if (!$this->userId) {
            return response()->json([
                'message' => 'Siswa tidak ditemukan',
                'status' => 'error'
            ], 404);
        }
        $kegiatan = KegiatanPrakerin::findOrFail($id);
        $kegiatan->update($request->all());
        if ($kegiatan) {
            return response()->json([
                'message' => 'Kegiatan prakerin berhasil diupdate',
                'status' => 'success',
                'data' => $kegiatan
            ]);
        }
        return response()->json([
            'message' => 'Kegiatan prakerin gagal diupdate',
            'status' => 'error'
        ], 400);
    }

    public function destroyKegiatanPrakerin($id)
    {
        if (!$this->userId) {
            return response()->json([
                'message' => 'Siswa tidak ditemukan',
                'status' => 'error'
            ], 404);
        }
        $kegiatan = KegiatanPrakerin::findOrFail($id);

        // Ambil semua gambar dari deskripsi
        preg_match_all('/<img[^>]+src="([^">]+)"/', $kegiatan->deskripsi, $images);
        $images = $images[1] ?? [];

        // Hapus semua file gambar yang dipakai di deskripsi
        foreach ($images as $img) {
            $path = str_replace(url('storage') . '/', '', $img);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        // Hapus record kegiatan
        $kegiatan->delete();

        if ($kegiatan) {
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
