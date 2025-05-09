<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            // Check if the user already exists
            $existingUserEmail = User::where('email', $row['email'])->first();
            if ($existingUserEmail) {
                return response()->json([
                    'message' => 'Email already exists',
                    'status' => 'error',
                    'code' => 422,
                ]);
            }
            // Check if the NIS already exists
            $existingNis = Siswa::where('nis', $row['nis'])->first();
            if ($existingNis) {
                return response()->json([
                    'message' => 'NIS already exists',
                    'status' => 'error',
                    'code' => 422,
                ]);
            }
            $user = User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make($row['nis']),
                'roles' => 'siswa',
            ]);

            $siswa = Siswa::create([
                'user_id' => $user->id,
                'nama' => $row['nama'],
                'nis' => $row['nis'],
                'kelas' => $row['kelas'],
                'jurusan' => $row['jurusan'],
                'no_hp' => $row['no_hp'],
                'alamat' => $row['alamat'],
                'foto_wajah' => $row['foto_wajah'] ?? null,
                'lokasi_prakerin_id' => rand(1, 10) ?? null,
            ]);
        }
    }
}
