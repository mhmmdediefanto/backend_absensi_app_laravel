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
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'nis' => 'required'
        ]);

        $nisSiswa = $request->input('nis');

        if ($request->hasFile('image')) {
            // Simpan sementara
            $path = $request->file('image')->store("temp", 'public');
            $fullPath = storage_path("app/public/" . $path);
            // Panggil Python untuk crop & resize + simpan ke dataset
            $pythonScript = base_path('face_recognition/save_dataset.py');
            $command = "python \"$pythonScript\" \"$fullPath\" $nisSiswa 2>&1";
            $output = shell_exec($command);

            $result = json_decode($output, true);
            return response()->json($result);
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
