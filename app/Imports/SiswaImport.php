<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        DB::enableQueryLog();
        // Ambil semua email dan NIS yang sudah ada di database
        $existingEmails = User::pluck('email')->toArray();
        $existingNis = Siswa::pluck('nis')->toArray();

        $usersToInsert = [];
        $siswasToInsert = [];
        $errors = [];
        $now = now();

        // Deteksi duplikat di file (CSV) dengan hashmap
        $fileEmails = [];
        $fileNis = [];

        foreach ($rows as $index => $row) {
            // Cek duplikat di file (CSV) dengan hashmap
            if (isset($fileEmails[$row['email']])) {
                $errors[] = [
                    'row' => $index + 2,
                    'message' => 'Duplicate email in file: ' . $row['email'],
                ];
                continue;
            }
            if (isset($fileNis[$row['nis']])) {
                $errors[] = [
                    'row' => $index + 2,
                    'message' => 'Duplicate NIS in file: ' . $row['nis'],
                ];
                continue;
            }
            $fileEmails[$row['email']] = true;
            $fileNis[$row['nis']] = true;

            // Cek duplikat di database
            if (in_array($row['email'], $existingEmails)) {
                $errors[] = [
                    'row' => $index + 2, // +2 karena heading row dan index mulai 0
                    'message' => 'Email already exists: ' . $row['email'],
                ];
                continue;
            }
            if (in_array($row['nis'], $existingNis)) {
                $errors[] = [
                    'row' => $index + 2,
                    'message' => 'NIS already exists: ' . $row['nis'],
                ];
                continue;
            }
            $usersToInsert[] = [
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make($row['nis']),
                'roles' => 'siswa',
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

        // Siapkan data siswa untuk insert massal
        foreach ($insertedUsers as $user) {
            // Cari row yang sesuai
            foreach ($rows as $row) {
                if ($row['email'] === $user->email) {
                    $siswasToInsert[] = [
                        'user_id' => $user->id,
                        'nama' => $row['nama'],
                        'nis' => $row['nis'],
                        'kelas' => $row['kelas'],
                        'jurusan' => $row['jurusan'],
                        'no_hp' => $row['no_hp'],
                        'alamat' => $row['alamat'],
                        'foto_wajah' => $row['foto_wajah'] ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    break;
                }
            }
        }

        if (!empty($siswasToInsert)) {
            Siswa::insert($siswasToInsert);
        }

        if (!empty($errors)) {
            // Kembalikan semua error sekaligus
            throw new \Exception(json_encode($errors));
        }
        // Tampilkan query log untuk debug bulk insert
        // dd(DB::getQueryLog());
    }
}
