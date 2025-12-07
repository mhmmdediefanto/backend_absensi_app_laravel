<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensis;
use App\Models\PembimbingSiswa;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiAnakBimbingController extends Controller
{
    protected function resolveGuruIds(): array
    {
        $user = Auth::user();

        if (!$user) {
            return [];
        }

        $ids = [];

        if ($user->guru?->id) {
            $ids[] = $user->guru->id;
        }

        $ids[] = $user->id;

        return array_values(array_unique(array_filter($ids)));
    }

    public function getAbsensiAnakBimbingToday()
    {
        $guruIds = $this->resolveGuruIds();

        if (empty($guruIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data guru tidak ditemukan.',
            ], 404);
        }

        $absensiSiswa = PembimbingSiswa::whereIn('guru_id', $guruIds)
            ->with([
                'siswa' => function ($query) {
                    $query->select('id', 'user_id', 'nama', 'nis', 'kelas', 'lokasi_prakerin_id');
                },
                'siswa.lokasiPrakerin:id,nama_instansi,alamat',
                'siswa.absensi' => function ($query) {
                    $query->whereDate('tanggal', now()->toDateString())
                        ->select('id', 'user_id', 'tanggal', 'time', 'status_keterlambatan', 'tipe_absen', 'keterangan');
                },
            ])
            ->get();

        return response()->json([
            'data' => $absensiSiswa,
            'status' => 'success',
            'message' => 'Data absensi anak bimbing berhasil diambil',
        ], 200);
    }

    public function historyAbsensiAnakBimbing(Request $request)
    {
        $guruIds = $this->resolveGuruIds();

        if (empty($guruIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data guru tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:Hadir,Alpa,Sakit,Izin'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $date = $validated['date'] ?? null;
        $status = $validated['status'] ?? null;
        $search = $validated['search'] ?? null;
        $perPage = $validated['per_page'] ?? 10;

        $siswaIds = PembimbingSiswa::whereIn('guru_id', $guruIds)->pluck('siswa_id');

        if ($siswaIds->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data absensi anak bimbing berhasil diambil',
                'data' => [
                    'items' => [],
                    'meta' => [
                        'current_page' => (int) ($validated['page'] ?? 1),
                        'last_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                    ],
                ],
            ], 200);
        }

        $siswaUserIds = Siswa::whereIn('id', $siswaIds)
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();

        if ($siswaUserIds->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data absensi anak bimbing berhasil diambil',
                'data' => [
                    'items' => [],
                    'meta' => [
                        'current_page' => (int) ($validated['page'] ?? 1),
                        'last_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                    ],
                ],
            ], 200);
        }

        $query = Absensis::with([
            'siswa:id,user_id,nama,nis,kelas,lokasi_prakerin_id',
            'siswa.lokasiPrakerin:id,nama_instansi,alamat',
        ])
            ->whereIn('user_id', $siswaUserIds)
            ->when($date, fn($q) => $q->whereDate('tanggal', $date))
            ->when(!$date, fn($q) => $q->whereDate('tanggal', '!=', Carbon::today()->toDateString()))
            ->when($status, fn($q) => $q->where('keterangan', $status))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('siswa', function ($query) use ($search) {
                    $query->where('nama', 'like', '%' . $search . '%')
                        ->orWhere('nis', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('time')
            ->orderByDesc('created_at');

        $attendance = $query->paginate($perPage);

        $items = $attendance->getCollection()->map(function (Absensis $record) {
            $tanggal = $record->tanggal ? Carbon::parse($record->tanggal) : null;
            $time = null;

            if ($record->time) {
                try {
                    $time = Carbon::createFromFormat('H:i:s', $record->time)->format('H:i');
                } catch (\Exception $e) {
                    $time = $record->time;
                }
            }

            return [
                'id' => $record->id,
                'tanggal' => $tanggal?->toDateString(),
                'tanggal_label' => $tanggal?->translatedFormat('d M Y'),
                'time' => $time,
                'tipe_absen' => $record->tipe_absen,
                'keterangan' => $record->keterangan,
                'status_keterlambatan' => $record->status_keterlambatan,
                'status_lokasi' => $record->status_lokasi,
                'status_wajah' => $record->status_wajah,
                'lokasi_prakerin_id' => $record->lokasi_prakerin_id,
                'lokasi' => [
                    'nama' => $record->siswa?->lokasiPrakerin?->nama_instansi,
                    'alamat' => $record->siswa?->lokasiPrakerin?->alamat,
                ],
                'siswa' => [
                    'user_id' => $record->user_id,
                    'nama' => $record->siswa?->nama,
                    'nis' => $record->siswa?->nis,
                    'kelas' => $record->siswa?->kelas,
                ],
            ];
        })->values();

        $meta = [
            'current_page' => $attendance->currentPage(),
            'last_page' => $attendance->lastPage(),
            'per_page' => $attendance->perPage(),
            'total' => $attendance->total(),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Data absensi anak bimbing berhasil diambil',
            'data' => [
                'items' => $items,
                'meta' => $meta,
            ],
        ], 200);
    }

    public function allAbsensiAnakBimbing(Request $request)
    {
        $guruIds = $this->resolveGuruIds();

        if (empty($guruIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data guru tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:Hadir,Alpa,Sakit,Izin'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $startDate = $validated['start_date'] ?? Carbon::today()->subDays(29)->toDateString();
        $endDate = $validated['end_date'] ?? Carbon::yesterday()->toDateString();
        $status = $validated['status'] ?? null;
        $search = $validated['search'] ?? null;
        $perPage = $validated['per_page'] ?? 15;

        $startDateCarbon = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        if ($endDateCarbon->greaterThanOrEqualTo(Carbon::today())) {
            $endDateCarbon = Carbon::yesterday();
        }

        if ($startDateCarbon->greaterThan($endDateCarbon)) {
            $startDateCarbon = (clone $endDateCarbon)->subDays(29);
        }

        $siswaIds = PembimbingSiswa::whereIn('guru_id', $guruIds)->pluck('siswa_id');

        if ($siswaIds->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data absensi anak bimbing berhasil diambil',
                'data' => [
                    'items' => [],
                    'meta' => [
                        'current_page' => (int) ($validated['page'] ?? 1),
                        'last_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                        'range' => [
                            'start_date' => $startDateCarbon->toDateString(),
                            'end_date' => $endDateCarbon->toDateString(),
                        ],
                    ],
                ],
            ], 200);
        }

        $siswaUserIds = Siswa::whereIn('id', $siswaIds)
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();

        if ($siswaUserIds->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data absensi anak bimbing berhasil diambil',
                'data' => [
                    'items' => [],
                    'meta' => [
                        'current_page' => (int) ($validated['page'] ?? 1),
                        'last_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                        'range' => [
                            'start_date' => $startDateCarbon->toDateString(),
                            'end_date' => $endDateCarbon->toDateString(),
                        ],
                    ],
                ],
            ], 200);
        }

        $query = Absensis::with([
            'siswa:id,user_id,nama,nis,kelas,lokasi_prakerin_id',
            'siswa.lokasiPrakerin:id,nama_instansi,alamat',
        ])
            ->whereIn('user_id', $siswaUserIds)
            ->whereBetween('tanggal', [$startDateCarbon->toDateString(), $endDateCarbon->toDateString()])
            ->when($status, fn($q) => $q->where('keterangan', $status))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('siswa', function ($query) use ($search) {
                    $query->where('nama', 'like', '%' . $search . '%')
                        ->orWhere('nis', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('time')
            ->orderByDesc('created_at');

        $attendance = $query->paginate($perPage);

        $items = $attendance->getCollection()->map(function (Absensis $record) {
            $tanggal = $record->tanggal ? Carbon::parse($record->tanggal) : null;
            $time = null;

            if ($record->time) {
                try {
                    $time = Carbon::createFromFormat('H:i:s', $record->time)->format('H:i');
                } catch (\Exception $e) {
                    $time = $record->time;
                }
            }

            return [
                'id' => $record->id,
                'tanggal' => $tanggal?->toDateString(),
                'tanggal_label' => $tanggal?->translatedFormat('d M Y'),
                'time' => $time,
                'tipe_absen' => $record->tipe_absen,
                'keterangan' => $record->keterangan,
                'status_keterlambatan' => $record->status_keterlambatan,
                'status_lokasi' => $record->status_lokasi,
                'status_wajah' => $record->status_wajah,
                'lokasi_prakerin_id' => $record->lokasi_prakerin_id,
                'lokasi' => [
                    'nama' => $record->siswa?->lokasiPrakerin?->nama_instansi,
                    'alamat' => $record->siswa?->lokasiPrakerin?->alamat,
                ],
                'siswa' => [
                    'user_id' => $record->user_id,
                    'nama' => $record->siswa?->nama,
                    'nis' => $record->siswa?->nis,
                    'kelas' => $record->siswa?->kelas,
                ],
            ];
        })->values();

        $meta = [
            'current_page' => $attendance->currentPage(),
            'last_page' => $attendance->lastPage(),
            'per_page' => $attendance->perPage(),
            'total' => $attendance->total(),
            'range' => [
                'start_date' => $startDateCarbon->toDateString(),
                'end_date' => $endDateCarbon->toDateString(),
            ],
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Data absensi anak bimbing berhasil diambil',
            'data' => [
                'items' => $items,
                'meta' => $meta,
            ],
        ], 200);
    }
}
