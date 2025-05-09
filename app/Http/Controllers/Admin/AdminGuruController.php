<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\GuruImport;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class AdminGuruController extends Controller
{
    public function getGuru()
    {
        $guru = Guru::select('nama', 'nip', 'no_hp', 'jabatan')->latest()->paginate(10);
        return response()->json([
            'message' => 'Guru retrieved successfully',
            'status' => 'success',
            'code' => 200,
            'data' => $guru,
        ]);
    }

    public function createGuru(Request $request)
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'roles' => 'nullable|string|in:guru',
                'nama' => 'required|string|max:255',
                'nip' => 'required|string|max:255|unique:gurus,nip',
                'no_hp' => 'required|string|max:255',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'jabatan' => 'required|string|max:255',
            ]);

            $cekEmailGuru = User::where('email', $request->email)->first();
            if ($cekEmailGuru) {
                return response()->json([
                    'message' => 'Email already exists',
                    'status' => 'error',
                    'code' => 422,
                ]);
            }

            $cekNipGuru = Guru::where('nip', $request->nip)->first();
            if ($cekNipGuru) {
                return response()->json([
                    'message' => 'NIP already exists',
                    'status' => 'error',
                    'code' => 422,
                ]);
            }
            if ($request->hasFile('foto')) {
                $validatedData['foto'] = $request->file('foto')->store('uploads/guru', 'public');
            }


            $users = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'roles' => $validatedData['roles'] ?? 'guru',
            ]);
            $guru = Guru::create([
                'user_id' => $users->id,
                'nama' => $validatedData['nama'],
                'nip' => $validatedData['nip'],
                'no_hp' => $validatedData['no_hp'],
                'jabatan' => $validatedData['jabatan'],
                'foto' => $validatedData['foto'] ?? null,
            ]);
            DB::commit();

            return response()->json([
                'message' => 'Guru created successfully',
                'status' => 'success',
                'code' => 201,
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 'error',
                'code' => 500,
            ]);
        }
    }

    public function delete($id)
    {
        $guru = Guru::find($id);
        if ($guru) {
            if ($guru->foto) {
                Storage::disk('public')->delete($guru->foto);
            }

            $user = User::find($guru->user_id);
            if ($user) {
                $user->delete();
            }
            $guru->delete();
            return response()->json([
                'message' => 'Guru deleted successfully',
            ]);
        } else {
            return response()->json([
                'message' => 'Guru not found',
            ], 404);
        }
    }

    public function importGuru(Request $request)
    {
        try {

            DB::beginTransaction();
            $validatedData = $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            ]);



            $filesGuru = Excel::import(new GuruImport, $validatedData['file']);
            if ($filesGuru) {
                DB::commit();
                return response()->json([
                    'message' => 'Guru imported successfully',
                    'status' => 'success',
                    'code' => 200,
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => $th->getMessage(),
                'status' => 'error',
                'code' => 500,
            ]);
        }
    }
}
