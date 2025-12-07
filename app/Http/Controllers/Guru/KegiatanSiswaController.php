<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\KegiatanPrakerin;
use App\Models\PembimbingSiswa;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KegiatanSiswaController extends Controller
{
    /**
     * Ambil daftar kegiatan prakerin siswa binaan.
     */
    public function index(Request $request)
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
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status_prakerin' => ['nullable', 'in:aktif,nonaktif,selesai,dibatalkan'],
            'search' => ['nullable', 'string', 'max:150'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $baseEnd = Carbon::today();
        $baseStart = (clone $baseEnd)->subDays(6);

        if (!empty($validated['date'])) {
            $baseStart = Carbon::parse($validated['date'])->startOfDay();
            $baseEnd = Carbon::parse($validated['date'])->endOfDay();
        }

        if (!empty($validated['start_date'])) {
            $baseStart = Carbon::parse($validated['start_date'])->startOfDay();
        }

        if (!empty($validated['end_date'])) {
            $baseEnd = Carbon::parse($validated['end_date'])->endOfDay();
        }

        $siswaIds = PembimbingSiswa::whereIn('guru_id', $guruIds)->pluck('siswa_id');

        if ($siswaIds->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data kegiatan berhasil diambil',
                'data' => [
                    'items' => [],
                    'meta' => [
                        'current_page' => (int) ($validated['page'] ?? 1),
                        'last_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                        'range' => [
                            'start_date' => $baseStart->toDateString(),
                            'end_date' => $baseEnd->toDateString(),
                        ],
                    ],
                ],
            ]);
        }

        $query = KegiatanPrakerin::with([
            'siswa:id,nama,nis,kelas,lokasi_prakerin_id,status_prakerin',
            'siswa.lokasiPrakerin:id,nama_instansi,alamat',
        ])
            ->whereIn('siswa_id', $siswaIds)
            ->whereBetween('tanggal', [$baseStart->toDateString(), $baseEnd->toDateString()])
            ->when(
                !empty($validated['status_prakerin']),
                fn($q) => $q->whereHas('siswa', function ($query) use ($validated) {
                    $query->where('status_prakerin', $validated['status_prakerin']);
                })
            )
            ->when(
                !empty($validated['search']),
                function ($q) use ($validated) {
                    $search = $validated['search'];
                    $q->where(function ($query) use ($search) {
                        $query->where('judul', 'like', '%' . $search . '%')
                            ->orWhere('deskripsi', 'like', '%' . $search . '%')
                            ->orWhereHas('siswa', function ($siswaQuery) use ($search) {
                                $siswaQuery->where('nama', 'like', '%' . $search . '%')
                                    ->orWhere('nis', 'like', '%' . $search . '%');
                            });
                    });
                }
            )
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at');

        $activities = $query->paginate($perPage);

        $items = $activities->getCollection()->map(function (KegiatanPrakerin $kegiatan) {
            $tanggal = $kegiatan->tanggal ? Carbon::parse($kegiatan->tanggal) : null;

            return [
                'id' => $kegiatan->id,
                'tanggal' => $tanggal?->toDateString(),
                'tanggal_label' => $tanggal?->translatedFormat('d M Y'),
                'judul' => $kegiatan->judul,
                'ringkasan' => strip_tags(Str::limit($kegiatan->deskripsi ?? '', 160)),
                'siswa' => [
                    'id' => $kegiatan->siswa?->id,
                    'nama' => $kegiatan->siswa?->nama,
                    'nis' => $kegiatan->siswa?->nis,
                    'kelas' => $kegiatan->siswa?->kelas,
                    'status_prakerin' => $kegiatan->siswa?->status_prakerin,
                ],
                'lokasi' => [
                    'nama' => $kegiatan->siswa?->lokasiPrakerin?->nama_instansi,
                    'alamat' => $kegiatan->siswa?->lokasiPrakerin?->alamat,
                ],
            ];
        })->values();

        $meta = [
            'current_page' => $activities->currentPage(),
            'last_page' => $activities->lastPage(),
            'per_page' => $activities->perPage(),
            'total' => $activities->total(),
            'range' => [
                'start_date' => $baseStart->toDateString(),
                'end_date' => $baseEnd->toDateString(),
            ],
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Data kegiatan berhasil diambil',
            'data' => [
                'items' => $items,
                'meta' => $meta,
            ],
        ]);
    }

    /**
     * Ambil detail kegiatan tertentu.
     */
    public function show($id)
    {
        $guruIds = $this->resolveGuruIds();

        if (empty($guruIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data guru tidak ditemukan.',
            ], 404);
        }

        $siswaIds = PembimbingSiswa::whereIn('guru_id', $guruIds)->pluck('siswa_id');

        if ($siswaIds->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kegiatan tidak ditemukan.',
            ], 404);
        }

        $kegiatan = KegiatanPrakerin::with([
            'siswa:id,nama,nis,kelas,lokasi_prakerin_id,status_prakerin',
            'siswa.lokasiPrakerin:id,nama_instansi,alamat',
        ])
            ->whereIn('siswa_id', $siswaIds)
            ->find($id);

        if (!$kegiatan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kegiatan tidak ditemukan.',
            ], 404);
        }

        $tanggal = $kegiatan->tanggal ? Carbon::parse($kegiatan->tanggal) : null;

        return response()->json([
            'status' => 'success',
            'message' => 'Detail kegiatan berhasil diambil',
            'data' => [
                'id' => $kegiatan->id,
                'tanggal' => $tanggal?->toDateString(),
                'tanggal_label' => $tanggal?->translatedFormat('d M Y'),
                'judul' => $kegiatan->judul,
                'deskripsi' => $kegiatan->deskripsi,
                'catatan_guru' => $kegiatan->catatan_guru,
                'siswa' => [
                    'id' => $kegiatan->siswa?->id,
                    'nama' => $kegiatan->siswa?->nama,
                    'nis' => $kegiatan->siswa?->nis,
                    'kelas' => $kegiatan->siswa?->kelas,
                    'status_prakerin' => $kegiatan->siswa?->status_prakerin,
                ],
                'lokasi' => [
                    'nama' => $kegiatan->siswa?->lokasiPrakerin?->nama_instansi,
                    'alamat' => $kegiatan->siswa?->lokasiPrakerin?->alamat,
                ],
            ],
        ]);
    }

    /**
     * Kumpulkan seluruh ID guru yang relevan (record guru dan user id fallback).
     */
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
}


