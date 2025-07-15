<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LokasiPrakerin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminLokasiPrakerinController extends Controller
{
    public function getAllLokasiPrakerin()
    {
        $lokasiPrakerin = LokasiPrakerin::select('id', 'nama_instansi', 'alamat', 'no_telp', 'email', 'website', 'latitude', 'longitude')
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Data lokasi prakerin retrieved successfully',
            'status' => 'success',
            'code' => 200,
            'data' => $lokasiPrakerin,
        ]);
    }

    public function createLokasiPrakerin(Request $request)
    {

        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'nama_instansi' => 'required|string|max:255',
                'alamat' => 'required|string|max:255',
                'no_telp' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'website' => 'nullable|url|max:255',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'kontak_person' => 'nullable|string|max:255',
                'no_tlp' => 'nullable|string|max:20',
                'bidang_usaha' => 'nullable|string|max:255',
                'kapasitas_siswa' => 'nullable',
                'mulai_kerjasama' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'status' => 'error',
                    'code' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validatedData = $validator->validated();

            // Check if the location already exists
            $existingLocation = LokasiPrakerin::where('nama_instansi', $validatedData['nama_instansi'])
                ->where('alamat', $validatedData['alamat'])
                ->first();

            if ($existingLocation) {
                return response()->json([
                    'message' => 'Data lokasi prakerin already exists',
                    'status' => 'error',
                    'code' => 409,
                ], 409);
            }

            // Create a new location
            LokasiPrakerin::create([
                'nama_instansi' => $validatedData['nama_instansi'],
                'alamat' => $validatedData['alamat'],
                'no_telp' => $validatedData['no_telp'],
                'email' => $validatedData['email'],
                'website' => $validatedData['website'],
                'latitude' => $validatedData['latitude'],
                'longitude' => $validatedData['longitude'],
                'kontak_person' => $validatedData['kontak_person'] ?? null,
                'no_tlp' => $validatedData['no_tlp'] ?? null,
                'bidang_usaha' => $validatedData['bidang_usaha'] ?? null,
                'kapasitas_siswa' => $validatedData['kapasitas_siswa'] ?? null,
                'mulai_kerjasama' => $validatedData['mulai_kerjasama'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Data lokasi prakerin created successfully',
                'status' => 'success',
                'code' => 201,
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
}
