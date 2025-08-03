<?php

namespace App\Http\Controllers\Siswa;

use App\Helpers\HaversineFormula;
use App\Http\Controllers\Controller;
use App\Models\Absensis;
use App\Models\LokasiPrakerin;
use App\Models\Siswa;
use App\Services\AbsensiService;
use App\Services\FaceRecognizeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AbsensiController extends Controller
{
    protected $faceRecognizeService;
    protected $absensiService;
    public $hariIni;

    public function __construct(FaceRecognizeService $faceRecognizeService, AbsensiService $absensiService)
    {
        $this->faceRecognizeService = $faceRecognizeService;
        $this->absensiService = $absensiService;
        $this->hariIni = now()->locale('id')->isoFormat('dddd');
    }

    public function getRiwayatAbsensi()
    {

        $useId = Auth::id();
        $cacheKey = 'riwayat_absensi_user_' . $useId;
        $absensi = Cache::remember($cacheKey, 60 * 6, function () use ($useId) {
            return Absensis::where('user_id', $useId)->where('tanggal', '!=', now()->toDateString())->get();
        });

        if ($absensi->isEmpty()) {
            return response()->json([
                'message' => 'Riwayat absensi belum ada',
                'code' => 201
            ], 201);
        }

        return response()->json([
            'message' => 'Riwayat absensi',
            'code' => 200,
            'data' => $absensi
        ], 200);

    }

    public function getAbsensiToday()
    {

        // ambil hari ini
        $hariIni = $this->hariIni;

        // cek apakah ada jadwal prakerin hari ini
        $siswa = Siswa::where('user_id', Auth::id())->first();

        if (!$siswa) {
            return response()->json([
                'message' => 'Data siswa tidak ditemukan',
                'code' => 404
            ], 404);
        }

        $lokasiPrakerins = $siswa->load(['lokasiPrakerin.jadwalPrakerin' => function ($query) use ($hariIni) {
            $query->where('hari', $hariIni);
            $query->where('status_aktif', true);
        }]);

        if (!$lokasiPrakerins->lokasiPrakerin || $lokasiPrakerins->lokasiPrakerin->jadwalPrakerin->isEmpty()) {
            return response()->json([
                'message' => 'Hari ini tidak ada jadwal prakerin / Libur',
                'code' => 201
            ], 201);
        }

        $absensi = Absensis::select('id', 'lokasi_prakerin_id', 'user_id', 'time', 'tipe_absen', 'status_keterlambatan', 'keterangan')
            ->where('user_id', Auth::id())
            ->where('tanggal', now()->toDateString())->get();

        if ($absensi->isEmpty()) {
            return response()->json([
                'message' => 'Absensi hari ini belum dilakukan',
                'data' => $lokasiPrakerins->lokasiPrakerin->jadwalPrakerin,
                'code' => 200
            ], 200);
        }

        return response()->json([
            'message' => 'Absensi hari ini',
            'code' => 200,
            'data' => $absensi
        ], 200);
    }

    public function absensi(Request $request)
    {
        $request->validate([
            'lokasi_prakerin_id' => 'required|exists:lokasi_prakerins,id',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'foto' => 'required|image|mimes:jpeg,jpg|max:4096',
            'tipe_absen' => 'required|in:masuk,pulang',
            'bypass_time' => 'boolean|nullable', // Optional parameter untuk testing
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

        // return $result;

        if (!$result['success'] || $result['data'][0]['confidence'] >= 90) {
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

        if ($distance > $lokasi->radius_absen) {
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
