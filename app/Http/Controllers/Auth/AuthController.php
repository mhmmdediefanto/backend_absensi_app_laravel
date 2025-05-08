<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function authenticate(Request $request)
    {
        try {
            $request->validate([
                'login' => 'required|string',
                'password' => 'required|string'
            ]);

            $login = $request->login;
            $password = $request->password;
            $user = null;

            // Cek apakah login berdasarkan email
            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $user = User::where('email', $login)->first();
                // dd($user);
            } else {
                // Bukan email: coba cari dari relasi guru berdasarkan NIP
                $guru = Guru::where('nip', $login)->with('user')->first();
                if ($guru) {
                    $user = $guru->user;
                }

                // Jika belum ketemu, coba cari dari relasi siswa berdasarkan NIS
                if (!$user) {
                    $siswa = Siswa::where('nis', $login)->with('user')->first();
                    if ($siswa) {
                        $user = $siswa->user;
                    }
                }
            }

            // Validasi password
            if (!$user || !Hash::check($password, $user->password)) {
                return response()->json([
                    'message' => 'Email / NIP / NIS atau password salah'
                ], 401);
            }


            // Hapus token lama dan buat yang baru
            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function me()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            unset($user->password, $user->created_at, $user->updated_at);

            $user->load('siswa', 'guru');

            return response()->json([
                'message' => 'Berhasil',
                'user' => $user
            ], 200);
        } catch (\Throwable $th) {

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = User::find($request->user()->id);
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            // Hapus semua token yang dimiliki user
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Logout berhasil'
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
