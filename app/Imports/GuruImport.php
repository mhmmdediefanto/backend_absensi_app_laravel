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
        // Ambil semua email dan NIP yang sudah ada di database
        $existingEmails = User::pluck('email')->toArray();
        $existingNip = Guru::pluck('nip')->toArray();

        $usersToInsert = [];
        $gurusToInsert = [];
        $errors = [];
        $now = now();

        // Deteksi duplikat di file (CSV) dengan hashmap
        $fileEmails = [];
        $fileNip = [];

        foreach ($rows as $index => $row) {
            // Cek duplikat di file (CSV) dengan hashmap
            if (isset($fileEmails[$row['email']])) {
                $errors[] = [
                    'row' => $index + 2,
                    'message' => 'Duplicate email in file: ' . $row['email'],
                ];
                continue;
            }
            if (isset($fileNip[$row['nip']])) {
                $errors[] = [
                    'row' => $index + 2,
                    'message' => 'Duplicate NIP in file: ' . $row['nip'],
                ];
                continue;
            }
            $fileEmails[$row['email']] = true;
            $fileNip[$row['nip']] = true;

            // Cek duplikat di database
            if (in_array($row['email'], $existingEmails)) {
                $errors[] = [
                    'row' => $index + 2,
                    'message' => 'Email already exists: ' . $row['email'],
                ];
                continue;
            }
            if (in_array($row['nip'], $existingNip)) {
                $errors[] = [
                    'row' => $index + 2,
                    'message' => 'NIP already exists: ' . $row['nip'],
                ];
                continue;
            }
            $usersToInsert[] = [
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make($row['nip']),
                'roles' => 'guru',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert users secara massal
        $insertedUsers = [];
        if (!empty($usersToInsert)) {
            User::insert($usersToInsert);
            // Ambil user yang baru diinsert
            $insertedUsers = User::whereIn('email', array_column($usersToInsert, 'email'))->get();
        }

        // Siapkan data guru untuk insert massal
        foreach ($insertedUsers as $user) {
            foreach ($rows as $row) {
                if ($row['email'] === $user->email) {
                    $gurusToInsert[] = [
                        'user_id' => $user->id,
                        'nama' => $row['nama'],
                        'nip' => $row['nip'],
                        'no_hp' => $row['no_hp'],
                        'jabatan' => $row['jabatan'],
                        'foto' => $row['foto'] ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    break;
                }
            }
        }

        if (!empty($gurusToInsert)) {
            Guru::insert($gurusToInsert);
        }

        if (!empty($errors)) {
            throw new \Exception(json_encode($errors));
        }
    }
}
