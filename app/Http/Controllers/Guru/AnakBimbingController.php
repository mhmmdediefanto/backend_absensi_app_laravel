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
        $siswa = PembimbingSiswa::where('guru_id', Auth::user()->id)->select('id', 'siswa_id', 'guru_id')->with('siswa', function ($query) use ($request) {
            $query->select('id', 'nama', 'nis', 'kelas', 'lokasi_prakerin_id', 'status_prakerin');
            $query->with('lokasiPrakerin', function ($query) {
                $query->select('id', 'nama_instansi', 'alamat');
            });
            $query->when($request->search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        })->paginate(10);

        return response()->json([
            'data' => $siswa,
            'total_all' => $siswa->total(),
            'status' => 'success',
            'message' => 'Data anak bimbing berhasil diambil'
        ], 200);
    }


    public function getDetailAnakBimbing($id)
    {
        $siswa = Siswa::where('user_id', $id)->with('lokasiPrakerin', 'guruPembimbingLokasi')->first();
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
