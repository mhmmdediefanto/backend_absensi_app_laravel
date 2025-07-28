<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaController extends Controller
{
    public function registeredFace(Request $request)
    {
        $request->validate([
            'image' => 'mimes:jpg',
            'nis' => 'required'
        ]);

        $nisSiswa = $request->input('nis');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store("dataset/{$nisSiswa}", 'public');
            return response()->json(['success' => true, 'path' => $path]);
        }
        return response()->json(['error' => 'Tidak ada file dikirim'], 400);
    }


    public function getPembimbingSiswa()
    {
        $pembimbingSiswa = Siswa::where('user_id', Auth::user()->id)->with('guruPembimbingLokasi')->first();


        // Cek apakah guru pembimbing lokasi masih array kosong
        if (empty($pembimbingSiswa->guruPembimbingLokasi) || count($pembimbingSiswa->guruPembimbingLokasi) == 0) {
            return response()->json([
                'message' => 'Siswa belum punya guru pembimbing',
                'code' => 404
            ], 404);
        }
        return response()->json([
            'pembimbingSiswa' => $pembimbingSiswa,
            'status' => 'success',
            'message' => 'Data pembimbing siswa berhasil diambil'
        ], 200);
    }
}
