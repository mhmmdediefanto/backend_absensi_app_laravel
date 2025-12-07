<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensis;
use App\Models\GuruLokasi;
use App\Models\PembimbingSiswa;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardGuruController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user?->guru) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data guru tidak ditemukan untuk pengguna ini.',
            ], 404);
        }

        $guru = $user->guru;

        $pembimbingQuery = PembimbingSiswa::where('guru_id', $guru->id);
        $siswaIds = $pembimbingQuery->pluck('siswa_id');

        $students = Siswa::whereIn('id', $siswaIds)
            ->with('lokasiPrakerin:id,nama_instansi,alamat')
            ->get([
                'id',
                'user_id',
                'nama',
                'nis',
                'kelas',
                'status_prakerin',
                'lokasi_prakerin_id',
            ]);

        $studentUserIds = $students->pluck('user_id')->filter()->values();
        $totalStudents = $students->count();

        $today = Carbon::today();

        $attendanceToday = $studentUserIds->isNotEmpty()
            ? Absensis::whereIn('user_id', $studentUserIds)
                ->whereDate('tanggal', $today)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereIn('keterangan', ['Hadir', 'Sakit', 'Izin'])
                            ->where('tipe_absen', 'masuk');
                    })
                        ->orWhere('keterangan', 'Alpa');
                })
                ->select('keterangan', DB::raw('COUNT(DISTINCT user_id) as total'))
                ->groupBy('keterangan')
                ->pluck('total', 'keterangan')
            : collect();

        $lateToday = $studentUserIds->isNotEmpty()
            ? Absensis::whereIn('user_id', $studentUserIds)
                ->whereDate('tanggal', $today)
                ->where('tipe_absen', 'masuk')
                ->where('status_keterlambatan', 'terlambat')
                ->distinct('user_id')
                ->count('user_id')
            : 0;

        $recentAttendance = $studentUserIds->isNotEmpty()
            ? Absensis::with([
                'siswa:id,user_id,nama,nis,kelas,lokasi_prakerin_id',
                'siswa.lokasiPrakerin:id,nama_instansi,alamat',
            ])
                ->whereIn('user_id', $studentUserIds)
                ->orderByDesc('tanggal')
                ->orderByDesc('time')
                ->limit(8)
                ->get()
                ->map(function (Absensis $record) {
                    $tanggal = $record->tanggal ? Carbon::parse($record->tanggal) : null;
                    $time = $record->time ? Carbon::parse($record->time)->format('H:i') : null;

                    return [
                        'id' => $record->id,
                        'tanggal' => $tanggal?->toDateString(),
                        'tanggal_label' => $tanggal?->translatedFormat('d M Y'),
                        'time' => $time,
                        'keterangan' => $record->keterangan,
                        'tipe_absen' => $record->tipe_absen,
                        'status_keterlambatan' => $record->status_keterlambatan,
                        'siswa' => [
                            'nama' => $record->siswa?->nama,
                            'nis' => $record->siswa?->nis,
                            'kelas' => $record->siswa?->kelas,
                            'lokasi' => $record->siswa?->lokasiPrakerin?->nama_instansi,
                        ],
                    ];
                })
                ->values()
            : collect();

        $trendData = $this->buildAttendanceTrend($studentUserIds);

        $lokasiIds = GuruLokasi::where('guru_id', $guru->id)->pluck('lokasi_prakerin_id');

        $locations = $lokasiIds->isNotEmpty()
            ? $this->buildLocationSummaries($lokasiIds, $students)
            : collect();

        $statusDistribution = $students->groupBy('status_prakerin')->map->count()->all();

        return response()->json([
            'status' => 'success',
            'message' => 'Data dashboard guru berhasil dimuat.',
            'data' => [
                'summary' => [
                    'total_students' => $totalStudents,
                    'hadir_today' => (int) ($attendanceToday['Hadir'] ?? 0),
                    'alpa_today' => (int) ($attendanceToday['Alpa'] ?? 0),
                    'izin_today' => (int) ($attendanceToday['Izin'] ?? 0),
                    'sakit_today' => (int) ($attendanceToday['Sakit'] ?? 0),
                    'late_today' => $lateToday,
                ],
                'student_status_distribution' => $statusDistribution,
                'attendance_trend' => $trendData,
                'recent_attendance' => $recentAttendance,
                'locations' => $locations,
                'last_refreshed_at' => Carbon::now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Build the attendance trend for the last 7 days.
     */
    protected function buildAttendanceTrend($studentUserIds)
    {
        if ($studentUserIds->isEmpty()) {
            return collect();
        }

        $endDate = Carbon::today();
        $startDate = (clone $endDate)->subDays(6);

        $rawTrend = Absensis::whereIn('user_id', $studentUserIds)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->select(
                'tanggal',
                DB::raw("COUNT(DISTINCT CASE WHEN keterangan = 'Hadir' AND tipe_absen = 'masuk' THEN user_id END) as hadir"),
                DB::raw("COUNT(DISTINCT CASE WHEN keterangan = 'Alpa' THEN user_id END) as alpa"),
                DB::raw("COUNT(DISTINCT CASE WHEN keterangan = 'Sakit' AND tipe_absen = 'masuk' THEN user_id END) as sakit"),
                DB::raw("COUNT(DISTINCT CASE WHEN keterangan = 'Izin' AND tipe_absen = 'masuk' THEN user_id END) as izin")
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        return collect(range(0, 6))
            ->map(function ($offset) use ($startDate, $rawTrend) {
                $date = (clone $startDate)->addDays($offset);
                $record = $rawTrend[$date->toDateString()] ?? null;

                return [
                    'date' => $date->toDateString(),
                    'label' => $date->translatedFormat('d M'),
                    'hadir' => (int) ($record->hadir ?? 0),
                    'alpa' => (int) ($record->alpa ?? 0),
                    'sakit' => (int) ($record->sakit ?? 0),
                    'izin' => (int) ($record->izin ?? 0),
                ];
            })
            ->values();
    }

    /**
     * Build location summaries with schedule and student counts.
     */
    protected function buildLocationSummaries($lokasiIds, $students)
    {
        return Cache::remember(
            'guru-dashboard-locations-' . md5($lokasiIds->implode('-') . $students->pluck('id')->implode('-')),
            now()->addMinutes(5),
            function () use ($lokasiIds, $students) {
                return DB::table('lokasi_prakerins')
                    ->whereIn('id', $lokasiIds)
                    ->select('id', 'nama_instansi', 'alamat', 'radius_absen', 'status_aktif')
                    ->get()
                    ->map(function ($lokasi) use ($students) {
                        $perLokasiStudents = $students->where('lokasi_prakerin_id', $lokasi->id);

                        $jadwal = DB::table('jadwal_prakerins')
                            ->where('lokasi_prakerin_id', $lokasi->id)
                            ->orderBy('hari')
                            ->get(['id', 'hari', 'jam_masuk', 'jam_pulang', 'status_aktif']);

                        return [
                            'id' => $lokasi->id,
                            'nama_instansi' => $lokasi->nama_instansi,
                            'alamat' => $lokasi->alamat,
                            'radius_absen' => (float) $lokasi->radius_absen,
                            'status_aktif' => (bool) $lokasi->status_aktif,
                            'total_siswa' => $perLokasiStudents->count(),
                            'jadwal' => $jadwal->map(function ($item) {
                                return [
                                    'id' => $item->id,
                                    'hari' => $item->hari,
                                    'jam_masuk' => $item->jam_masuk,
                                    'jam_pulang' => $item->jam_pulang,
                                    'status_aktif' => (bool) $item->status_aktif,
                                ];
                            })->values(),
                        ];
                    })
                    ->values();
            }
        );
    }
}

