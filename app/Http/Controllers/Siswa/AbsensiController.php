<?php

namespace App\Http\Controllers\Siswa;

use App\Helpers\HaversineFormula;
use App\Http\Controllers\Controller;
use App\Models\LokasiPrakerin;
use App\Services\AbsensiService;
use App\Services\FaceRecognizeService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    protected $faceRecognizeService;
    protected $absensiService;

    public function __construct(FaceRecognizeService $faceRecognizeService, AbsensiService $absensiService)
    {
        $this->faceRecognizeService = $faceRecognizeService;
        $this->absensiService = $absensiService;
    }

    public function absensi(Request $request)
    {

        $request->validate([
            'lokasi_prakerin_id' => 'required|exists:lokasi_prakerins,id',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'foto' => 'required|image|mimes:jpeg,jpg|max:4096',
            'tipe_absen' => 'required|in:masuk,pulang',
        ]);


        // recognisasi wajah
        $result = $this->faceRecognizeService->recognize($request->file('foto'));
        // dd($result);
        if (!$result['success'] || $result['data'][0]['confidence'] > 80) {
            return response()->json([
                'message' => 'Wajah tidak terdeteksi atau tidak cocok',
                'code' => 400
            ], 400);
        }

        // ambil lokasi praktek
        $lokasi = LokasiPrakerin::where('id', $request->lokasi_prakerin_id)->first();

        if (!$lokasi) {
            return response()->json([
                'message' => 'Lokasi prakerin tidak ditemukan',
                'code' => 400
            ], 400);
        }

        $distance = HaversineFormula::distance(
            $request->lat,
            $request->lon,
            $lokasi->latitude,
            $lokasi->longitude
        );


        if ($distance > $lokasi->radius) {
            return response()->json([
                'message' => 'Anda tidak berada di lokasi prakerin',
                'code' => 400
            ], 400);
        }

        // ambil nis siswa
        $nisSiswa = $result['data'][0]['folder_id'];

        $absensi = $this->absensiService->absensi([
            'user_id' => Auth::user()->id,
            'lokasi_prakerin_id' => $request->lokasi_prakerin_id,
            'tanggal' => now()->toDateString(),
            'time' => now()->toTimeString(),
            'latitude' => $request->lat,
            'longitude' => $request->lon,
            'status_lokasi' => 'valid',
            'status_wajah' => 'valid',
            'tipe_absen' => $request->tipe_absen,
        ]);

        if (isset($absensi['error'])) {
            return response()->json([
                'message' => $absensi['error'],
                'code' => 400
            ], 400);
        }

        return response()->json([
            'message' => 'Absensi berhasil',
            'code' => 200,
            'data' => [
                'nis' => $nisSiswa,
                'distance' => $distance,
                'result' => $result
            ]
        ], 200);
    }
}
