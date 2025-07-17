<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\SiswaImport;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    public function getSiswa()
    {

        $siswa = Siswa::select('id', 'user_id', 'nama', 'nis', 'kelas', 'jurusan', 'no_hp', 'alamat')
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'email', 'roles');
            }])
            ->latest()->paginate(10);
        return response()->json([
            'message' => 'Data Siswa',
            'total_siswa' => $siswa->total(),
            'status' => 'success',
            'code' => 200,
            'data' => $siswa,
        ]);
    }

    public function createSiswa(Request $request)
    {
        try {

            DB::beginTransaction();

            $validatedData = $request->validate([
                'nama' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'nis' => 'required|string|max:255|unique:siswas,nis',
                'kelas' => 'required|string|max:255',
                'jurusan' => 'required|string|max:255',
                'no_hp' => 'nullable|string|max:15',
                'alamat' => 'nullable|string|max:255',
                'foto_wajah' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'email' => [
                    'unique' => 'Maaf Email sudah terdaftar'
                ],
                'nis' => [
                    'unique' => 'Maaf NIS sudah terdaftar'
                ]
            ]);

            // dd($validatedData);
            // Handle file upload if provided
            if ($request->hasFile('foto_wajah')) {
                $validatedData['foto_wajah'] = $request->file('foto_wajah')->store('uploads/siswa', 'public');
            } else {
                $validatedData['foto_wajah'] = null;
            }

            $validatedData['password'] = bcrypt($validatedData['nis']);
            $validatedData['roles'] = 'siswa';



            $user = User::create([
                'email' => $validatedData['email'],
                'password' => $validatedData['nis'],
                'name' => $validatedData['nama'],
                'roles' => $validatedData['roles'],
            ]);
            // $user->assignRole('siswa');

            $siswa = Siswa::create([
                'user_id' => $user->id,
                'nama' => $validatedData['nama'],
                'nis' => $validatedData['nis'],
                'kelas' => $validatedData['kelas'],
                'jurusan' => $validatedData['jurusan'],
                'no_hp' => $validatedData['no_hp'],
                'alamat' => $validatedData['alamat'],
                'foto_wajah' => $validatedData['foto_wajah'] ?? null,
            ]);


            DB::commit();
            return response()->json([
                'message' => 'Siswa created successfully',
                'status' => 'success',
                'code' => 201,
                'data' => [
                    'user' => $user,
                    'siswa' => $siswa,
                ],
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create siswa',
                'status' => 'error',
                'code' => 500,
                'data' => $th->getMessage(),
            ]);
        }
    }

    public function deleteSiswa($id)
    {
        $siswa = Siswa::find($id);

        if (!$siswa) {
            return response()->json([
                'message' => 'Siswa not found',
                'status' => 'error',
                'code' => 404,
            ]);
        }
        try {
            DB::beginTransaction();

            $user = User::findOrFail($siswa->user_id);


            // Optionally, delete the profile picture if it exists
            if ($siswa->foto_wajah) {
                Storage::disk('public')->delete($siswa->foto_wajah);
            }

            // Delete the siswa and user
            $siswa->delete();
            $user->delete();

            DB::commit();
            return response()->json([
                'message' => 'Siswa deleted successfully',
                'status' => 'success',
                'code' => 200,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete siswa',
                'status' => 'error',
                'code' => 500,
                'data' => $th->getMessage(),
            ]);
        }
    }

    public function detailSiswa($id)
    {

        $user = Siswa::with([
            'user' => function ($query) {
                $query->select('id', 'name', 'email', 'roles');
            },
        ])
            ->where('id', $id)
            ->select('user_id', 'lokasi_prakerin_id', 'nama', 'nis', 'kelas', 'jurusan', 'no_hp', 'alamat',)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Siswa Tidak Ditemukan',
                'status' => 'error',
                'code' => 404,
            ]);
        }

        try {

            return response()->json([
                'message' => 'Detail Siswa',
                'status' => 'success',
                'code' => 200,
                'data' => [
                    'user' => $user
                ],
            ]);
        } catch (\Throwable $th) {
            response()->json([
                'message' => 'Failed to get siswa',
                'status' => 'error',
                'code' => 500,
                'data' => $th->getMessage(),
            ]);
        }
    }

    public function siswaImport(Request $request)
    {

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);
        try {
            DB::beginTransaction();

            $importSiswa = Excel::import(new SiswaImport, $request->file('file'));

            if ($importSiswa) {
                DB::commit();
                return response()->json([
                    'message' => 'Siswa imported successfully',
                    'status' => 'success',
                    'code' => 200,
                    'data' => $importSiswa,
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Failed to import siswa',
                    'status' => 'error',
                    'code' => 500,
                    'data' => $importSiswa,
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to import siswa',
                'status' => 'error',
                'code' => 500,
                'data' => $th->getMessage(),
            ]);
        }
    }

    public function updateSiswa(Request $request, $id)
    {
        $siswa = Siswa::find($id);
        if (!$siswa) {
            return response()->json([
                'message' => 'Siswa not found',
                'status' => 'error',
                'code' => 404,
            ]);
        }
        $user = User::find($siswa->user_id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'status' => 'error',
                'code' => 404,
            ]);
        }
        try {
            DB::beginTransaction();
            $validatedData = $request->validate([
                'nama' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'nis' => 'required|string|max:255|unique:siswas,nis,' . $siswa->id,
                'kelas' => 'required|string|max:255',
                'jurusan' => 'required|string|max:255',
                'no_hp' => 'nullable|string|max:15',
                'alamat' => 'nullable|string|max:255',
                'foto_wajah' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Handle file upload if provided
            if ($request->hasFile('foto_wajah')) {
                // Delete old photo if exists
                if ($siswa->foto_wajah) {
                    Storage::disk('public')->delete($siswa->foto_wajah);
                }
                $validatedData['foto_wajah'] = $request->file('foto_wajah')->store('uploads/siswa', 'public');
            } else {
                $validatedData['foto_wajah'] = $siswa->foto_wajah;
            }

            // Update user
            $user->name = $validatedData['nama'];
            $user->email = $validatedData['email'];
            $user->save();

            // Update siswa
            $siswa->nama = $validatedData['nama'];
            $siswa->nis = $validatedData['nis'];
            $siswa->kelas = $validatedData['kelas'];
            $siswa->jurusan = $validatedData['jurusan'];
            $siswa->no_hp = $validatedData['no_hp'];
            $siswa->alamat = $validatedData['alamat'];
            $siswa->foto_wajah = $validatedData['foto_wajah'];
            $siswa->save();

            DB::commit();
            return response()->json([
                'message' => 'Siswa updated successfully',
                'status' => 'success',
                'code' => 200,
                'data' => [
                    'user' => $user,
                    'siswa' => $siswa,
                ],
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update siswa',
                'status' => 'error',
                'code' => 500,
                'data' => $th->getMessage(),
            ]);
        }
    }
}
