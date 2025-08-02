<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\LokasiPrakerinImport;
use App\Models\LokasiPrakerin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\JadwalPrakerin;
use Maatwebsite\Excel\Facades\Excel;

class AdminLokasiPrakerinController extends Controller
{
    public function getAllLokasiPrakerin(Request $request)
    {
        $search = $request->input('search');
        $lokasiPrakerin = LokasiPrakerin::select('*')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_instansi', 'like', "%{$search}%")
                        ->orWhere('alamat', 'like', "%{$search}%");
                });
            })->latest()->paginate(10);
        return response()->json([
            'message' => 'Data lokasi prakerin retrieved successfully',
            'status' => 'success',
            'total_lokasi' => $lokasiPrakerin->total(),
            'code' => 200,
            'data' => $lokasiPrakerin,
        ]);
    }

    public function detailLokasiPrakerin($id)
    {
        $lokasi = LokasiPrakerin::with('jadwaPrakerin')->find($id);
        if (!$lokasi) {
            return response()->json([
                'message' => 'Data lokasi prakerin not found',
                'status' => 'error',
                'code' => 404,
            ], 404);
        }
        return response()->json([
            'message' => 'Detail lokasi prakerin',
            'status' => 'success',
            'code' => 200,
            'data' => $lokasi,
        ]);
    }

    public function createLokasiPrakerin(Request $request)
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validate([
                'nama_instansi' => 'required|string|max:255',
                'alamat' => 'required|string|max:255',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'kontak_person' => 'nullable|string|max:255',
                'no_telp' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'website' => 'nullable|url|max:255',
                'bidang_usaha' => 'nullable|string|max:255',
                'kapasitas_siswa' => 'nullable|integer',
                'status_kerjasama' => 'nullable|boolean',
                'mulai_kerjasama' => 'required|date',
                'selesai_kerjasama' => 'required|date',
                'radius_absen' => 'nullable|numeric',
                'foto_instansi' => 'nullable|string|max:255',
                'status_aktif' => 'nullable|boolean',
                'deskripsi' => 'nullable|string',
                'jenis_instansi' => 'nullable|string|max:255',
                'jadwal' => 'required|array|min:1',
                'jadwal.*.hari' => 'required|string|max:10',
                'jadwal.*.jam_masuk' => 'required|date_format:H:i',
                'jadwal.*.jam_pulang' => 'required|date_format:H:i',
                'jadwal.*.toleransi_masuk' => 'nullable|integer',
                'jadwal.*.toleransi_pulang' => 'nullable|integer',
                'jadwal.*.status_aktif' => 'nullable|boolean',
                'jadwal.*.keterangan' => 'nullable|string',
            ]);
            $jadwalArr = $validatedData['jadwal'];
            unset($validatedData['jadwal']);
            $lokasi = LokasiPrakerin::create($validatedData);
            foreach ($jadwalArr as $jadwal) {
                JadwalPrakerin::create([
                    'lokasi_prakerin_id' => $lokasi->id,
                    'hari' => $jadwal['hari'],
                    'jam_masuk' => $jadwal['jam_masuk'],
                    'jam_pulang' => $jadwal['jam_pulang'],
                    'toleransi_masuk' => $jadwal['toleransi_masuk'] ?? 10,
                    'toleransi_pulang' => $jadwal['toleransi_pulang'] ?? 0,
                    'status_aktif' => $jadwal['status_aktif'] ?? true,
                    'keterangan' => $jadwal['keterangan'] ?? 'Jadwal input',
                ]);
            }
            DB::commit();
            return response()->json([
                'message' => 'Data lokasi prakerin created successfully beserta jadwal input',
                'status' => 'success',
                'code' => 201,
                'data' => $lokasi,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create lokasi prakerin',
                'status' => 'error',
                'code' => 500,
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateLokasiPrakerin(Request $request, $id)
    {
        // dd($request->all());
        $lokasi = LokasiPrakerin::find($id);
        if (!$lokasi) {
            return response()->json([
                'message' => 'Data lokasi prakerin not found',
                'status' => 'error',
                'code' => 404,
            ], 404);
        }
        $validator = $request->validate([
            'nama_instansi' => 'sometimes|required|string|max:255',
            'alamat' => 'sometimes|required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'kontak_person' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'bidang_usaha' => 'nullable|string|max:255',
            'kapasitas_siswa' => 'nullable|integer',
            'status_kerjasama' => 'nullable|boolean',
            'mulai_kerjasama' => 'required|date',
            'selesai_kerjasama' => 'required|date',
            'radius_absen' => 'nullable|numeric',
            'foto_instansi' => 'nullable|string|max:255',
            'status_aktif' => 'nullable|boolean',
            'deskripsi' => 'nullable|string',
            'jenis_instansi' => 'nullable|string|max:255',
            'jadwal' => 'required|array|min:1',
            'jadwal.*.hari' => 'required|string|max:10',
            'jadwal.*.jam_masuk' => 'required|date_format:H:i',
            'jadwal.*.jam_pulang' => 'required|date_format:H:i',
            'jadwal.*.toleransi_masuk' => 'nullable|integer',
            'jadwal.*.toleransi_pulang' => 'nullable|integer',
            'jadwal.*.status_aktif' => 'nullable|boolean',
            'jadwal.*.keterangan' => 'nullable|string',
        ]);

        // dd($validator);
        $lokasi->update($validator);

        // Sinkronisasi jadwal jika ada di request
        if ($request->has('jadwal')) {
            $lokasi->syncJadwal($request->jadwal);
        }

        return response()->json([
            'message' => 'Data lokasi prakerin updated successfully',
            'status' => 'success',
            'code' => 200,
            'data' => $lokasi,
        ]);
    }

    public function deleteLokasiPrakerin($id)
    {
        try {
            DB::beginTransaction();

            $lokasiPrakerin = LokasiPrakerin::find($id);
            if (!$lokasiPrakerin) {
                return response()->json([
                    'message' => 'Data lokasi prakerin not found',
                    'status' => 'error',
                    'code' => 404,
                ], 404);
            }
            $lokasiPrakerin->delete();

            DB::commit();

            return response()->json([
                'message' => 'Data lokasi prakerin deleted successfully',
                'status' => 'success',
                'code' => 200,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete lokasi prakerin',
                'status' => 'error',
                'code' => 500,
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function importLokasi(Request $request)
    {
        // return 'oke';
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {

            $importLokasiPrakerin =   Excel::import(new LokasiPrakerinImport, $request->file('file'));
            if ($importLokasiPrakerin) {
                DB::commit();
                return response()->json([
                    'message' => 'Siswa imported successfully',
                    'status' => 'success',
                    'code' => 200,
                ]);
            } else {
                return response()->json([
                    'message' => 'Failed to import Lokasi prakerin',
                    'status' => 'error',
                    'code' => 500,
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to import prakerin',
                'status' => 'error',
                'code' => 500,
                'data' => $th->getMessage(),
            ]);
        }
    }

    public function detailLokasi($id)
    {
        $lokasi = LokasiPrakerin::with('jadwalPrakerin')->find($id);
        if (!$lokasi) {
            return response()->json([
                'message' => 'ID tidak di temukan',
                'status' => 'error',
                'code' => 401,
            ]);
        }

        return response()->json([
            'message' => 'Lokasi Retrieved',
            'data' => $lokasi,
        ], 200);
    }
}
