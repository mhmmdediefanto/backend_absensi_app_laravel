<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PembimbingSiswa;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnakBimbingController extends Controller
{
    public function getAnakBimbing(Request $request)
    {
        $siswa = PembimbingSiswa::where('guru_id', Auth::user()->id)
            ->whereHas('siswa', function ($query) use ($request) {
                $query->when($request->search, function ($query, $search) {
                    $query->where('nama', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->with(['siswa' => function ($query) {
                $query->select('id', 'nama', 'nis', 'kelas', 'lokasi_prakerin_id', 'status_prakerin')
                    ->with('lokasiPrakerin:id,nama_instansi,alamat');
            }])
            ->select('id', 'siswa_id', 'guru_id')
            ->paginate(10);

        return response()->json([
            'data' => $siswa,
            'total_all' => $siswa->total(),
            'status' => 'success',
            'message' => 'Data anak bimbing berhasil diambil'
        ], 200);
    }


    public function getDetailAnakBimbing($nis)
    {
        $siswa = Siswa::where('nis', $nis)->with('lokasiPrakerin', 'guruPembimbingLokasi')->first();
        if (!$siswa) {
            return response()->json([
                'message' => 'Siswa tidak ditemukan',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'siswa' => $siswa,
            'status' => 'success',
            'message' => 'Data anak bimbing berhasil diambil'
        ], 200);
    }
}
