<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PenempatanSiswaController extends Controller
{
    public function update(Request $request)
    {
        // dd($request->all());
        try {
            $idSiswa = $request->id_siswa;
            $siswa = Siswa::find($idSiswa);
            if (!$siswa) {
                throw new \Exception('ID Siswa tidak ditemukan');
            }

            $validator = Validator::make($request->all(), [
                'guru_id' => 'required',
                'lokasi_prakerin_id' => 'required'
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $siswa->update([
                'guru_id' => $request->guru_id,
                'lokasi_prakerin_id' => $request->lokasi_prakerin_id,
            ]);

            return response()->json([
                'message' => 'Penempatan siswa berhasil ditambahkan'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
