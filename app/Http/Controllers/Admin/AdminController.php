<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function getSiswa()
    {

        $siswa = Siswa::select('id', 'user_id', 'nama', 'nis', 'kelas', 'jurusan')
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'email', 'roles');
            }])
            ->get();
        return response()->json([
            'message' => 'Data Siswa',
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
                'password' => 'required|string|min:8',
                'nis' => 'required|string|max:255',
                'kelas' => 'required|string|max:255',
                'jurusan' => 'required|string|max:255',
                'no_hp' => 'nullable|string|max:15',
                'alamat' => 'nullable|string|max:255',
                'foto_wajah' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // dd($validatedData);

            // Check if the user already exists
            $existingUserEmail = User::where('email', $validatedData['email'])->first();
            if ($existingUserEmail) {
                return response()->json([
                    'message' => 'Email already exists',
                    'status' => 'error',
                    'code' => 422,
                ]);
            }

            $existingUserNis = Siswa::where('nis', $validatedData['nis'])->first();
            if ($existingUserNis) {
                return response()->json([
                    'message' => 'NIS already exists',
                    'status' => 'error',
                    'code' => 422,
                ]);
            }
            // Handle file upload if provided
            if ($request->hasFile('foto_wajah')) {
                $validatedData['foto_wajah'] = $request->file('foto_wajah')->store('uploads/siswa', 'public');
            } else {
                $validatedData['foto_wajah'] = null;
            }

            $validatedData['password'] = bcrypt($validatedData['password']);
            $validatedData['roles'] = 'siswa';



            $user = User::create([
                'email' => $validatedData['email'],
                'password' => $validatedData['password'],
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

        $user = User::with([
            'siswa' => function ($query) {
                $query->select('id', 'user_id', 'nama', 'nis', 'kelas', 'jurusan');
            },
            'lokasi' => function ($query) {
                $query->select('id', 'user_id', 'nama_instansi', 'alamat');
            }
        ])
            ->where('id', $id)
            ->select('id', 'name', 'email', 'roles')
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
}
