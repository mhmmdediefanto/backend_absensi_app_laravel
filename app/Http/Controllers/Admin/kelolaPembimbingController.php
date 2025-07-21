<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\KelolaPembimbing;
use Illuminate\Http\Request;

class kelolaPembimbingController extends Controller
{

    public function getKelolaPembimbing()
    {
        $pembimbings = KelolaPembimbing::with(['guru' => function ($query) {
            $query->select('nama', 'nip', 'id', 'jabatan');
        }, 'lokasiPrakerin' => function ($query) {
            $query->select('nama_instansi', 'alamat', 'id', 'status_aktif');
        }])->select('guru_id', 'lokasi_prakerin_id', 'tahun_ajaran', 'id')->get();

        return response()->json([
            'message' => 'kelola pembimbing retrieved',
            'data' => $pembimbings
        ], 200);
    }

    public function addKelola(Request $request)
    {
        $validatedData = $request->validate([
            'guru_id' => 'required',
            'lokasi_prakerin_id' => 'required',
            'tahun_ajaran' => 'nullable'
        ]);

        // simpan ke database
        $kelolaPembimbings = KelolaPembimbing::create($validatedData);

        if ($kelolaPembimbings) {
            return response()->json([
                'message' => 'kelola pembimbing berhasil di tambahkan',
                'data' => $kelolaPembimbings
            ], 200);
        }
    }

    public function updated(Request $request, $id)
    {
        $kelolaPembimbings = KelolaPembimbing::find($id);
        if (!$kelolaPembimbings) {
            return response()->json([
                'message' => 'id tidak di temuka'
            ], 404);
        }

        $validatedData = $request->validate([
            'guru_id' => 'required',
            'lokasi_prakerin_id' => 'required',
            'tahun_ajaran' => 'nullable'
        ]);

        $kelolaPembimbings->update($validatedData);

        return response()->json([
            'message' => 'Kelola pembimbing berhasil di update'
        ], 200);
    }

    public function destroy($id)
    {
        $kelolaPembimbings = KelolaPembimbing::find($id);
        if (!$kelolaPembimbings) {
            return response()->json([
                'message' => 'id tidak di temuka'
            ], 404);
        }

        $kelolaPembimbings->delete();
        return response()->json([
            'message' => 'Kelola pembimbing berhasil di hapus'
        ], 200);

    }
}
