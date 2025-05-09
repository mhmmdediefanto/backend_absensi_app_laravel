<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GuruImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $existingUserEmail = User::where('email', $row['email'])->first();
            if ($existingUserEmail) {
                return response()->json([
                    'message' => 'Email already exists',
                    'status' => 'error',
                    'code' => 422,
                ]);
            }

            $existingNip = Guru::where('nip', $row['nip'])->first();
            if ($existingNip) {
                return response()->json([
                    'message' => 'NIP already exists',
                    'status' => 'error',
                    'code' => 422,
                ]);
            }

            $user = User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make($row['nip']),
                'roles' => 'guru',
            ]);
            $guru = Guru::create([
                'user_id' => $user->id,
                'nama' => $row['nama'],
                'nip' => $row['nip'],
                'no_hp' => $row['no_hp'],
                'jabatan' => $row['jabatan'],
                'foto' => $row['foto'] ?? null,
            ]);
        }
    }
}
