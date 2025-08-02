<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isEmpty;

class ProfileController extends Controller
{
    public function getProfile()
    {
        $siswa = Siswa::where('user_id', Auth::user()->id)->with('lokasiPrakerin', 'guruPembimbingLokasi','user')->first();

        if (!$siswa) {
            return response()->json([
                'message' => 'Siswa tidak ditemukan',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'siswa' => $siswa,
            'status' => 'success',
            'message' => 'Data siswa berhasil diambil'
        ], 200);
    }
}
