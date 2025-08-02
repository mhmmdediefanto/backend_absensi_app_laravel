<?php

namespace App\Http\Controllers\Siswa;

use App\Helpers\HaversineFormula;
use App\Http\Controllers\Controller;
use App\Models\LokasiPrakerin;
use App\Models\Siswa;
use App\Services\AbsensiService;
use App\Services\FaceRecognizeService;
use Illuminate\Http\Request;

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
            'bypass_time' => 'boolean', // Optional parameter untuk testing
        ]);

        // Validasi jadwal dan waktu absensi
        $bypassTime = $request->boolean('bypass_time', false);
        $checkAbsensi = $this->absensiService->checkAbsensi($request->tipe_absen, $request->lokasi_prakerin_id, $bypassTime);

        if (!$checkAbsensi['status']) {
            return response()->json([
                'message' => $checkAbsensi['message'],
                'code' => 400
            ], 400);
        }

        // Validasi face recognition
        $result = $this->faceRecognizeService->recognize($request->file('foto'));
        
        if (!$result['success'] || $result['data'][0]['confidence'] < 80) {
            return response()->json([
                'message' => 'Wajah tidak terdeteksi atau tidak cocok dengan data yang tersimpan',
                'code' => 400
            ], 400);
        }

        // Validasi lokasi prakerin
        $lokasi = LokasiPrakerin::where('id', $request->lokasi_prakerin_id)->first();

        if (!$lokasi) {
            return response()->json([
                'message' => 'Lokasi prakerin tidak ditemukan',
                'code' => 400
            ], 400);
        }

        // Validasi jarak lokasi
        $distance = HaversineFormula::distance(
            $request->lat,
            $request->lon,
            $lokasi->latitude,
            $lokasi->longitude
        );

        if ($distance > $lokasi->radius) {
            return response()->json([
                'message' => 'Anda tidak berada di lokasi prakerin. Jarak: ' . round($distance, 2) . ' meter',
                'code' => 400
            ], 400);
        }

        // Ambil NIS siswa dari hasil face recognition
        $nisSiswa = $result['data'][0]['folder_id'];
        $userId = Siswa::where('nis', $nisSiswa)->first()->user_id;

        // Simpan data absensi
        $absensiData = [
            'user_id' => $userId,
            'lokasi_prakerin_id' => $request->lokasi_prakerin_id,
            'tanggal' => now()->toDateString(),
            'time' => now()->toTimeString(),
            'latitude' => $request->lat,
            'longitude' => $request->lon,
            'status_lokasi' => 'valid',
            'status_wajah' => 'valid',
            'tipe_absen' => $request->tipe_absen,
        ];

        // Tambahkan status keterlambatan jika ada
        if (isset($checkAbsensi['terlambat']) && $checkAbsensi['terlambat']) {
            $absensiData['status_keterlambatan'] = 'terlambat';
            $absensiData['keterangan'] = "Hadir";
        } else {
            $absensiData['keterangan'] = "Hadir";
            $absensiData['status_keterlambatan'] = 'tepat_waktu';
        }

        $this->absensiService->absensi($absensiData);

        $responseMessage = 'Absensi ' . $request->tipe_absen . ' berhasil';
        if (isset($checkAbsensi['terlambat']) && $checkAbsensi['terlambat']) {
            $responseMessage .= ' (Terlambat)';
        }

        return response()->json([
            'message' => $responseMessage,
            'code' => 200,
            'data' => [
                'nis' => $nisSiswa,
                'distance' => round($distance, 2),
                'confidence' => $result['data'][0]['confidence'],
                'terlambat' => $checkAbsensi['terlambat'] ?? false
            ]
        ], 200);
    }
}
