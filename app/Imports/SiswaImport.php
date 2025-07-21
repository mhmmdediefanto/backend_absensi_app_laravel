<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class SiswaImport implements ToModel, WithHeadingRow, WithChunkReading
{
    protected $existingEmails = [];
    protected $existingNis = [];
    protected $defaultPassword;

    public function __construct()
    {
        // Cache semua email dan NIS dari database
        $this->existingEmails = User::pluck('email')->toArray();
        $this->existingNis = Siswa::pluck('nis')->toArray();
        $this->defaultPassword = Hash::make('Skahada2');
    }

    public function model(array $row)
    {
        try {
            if (empty($row['email']) || empty($row['nis']) || empty($row['nama'])) {
                throw new \Exception('Wajib isi kolom: email / nis / nama');
            }

            if (in_array($row['email'], $this->existingEmails)) {
                throw new \Exception('Email sudah ada: ' . $row['email']);
            }

            if (in_array($row['nis'], $this->existingNis)) {
                throw new \Exception('NIS sudah ada: ' . $row['nis']);
            }

            // Tambahkan ke array agar tidak dobel di batch berikutnya
            $this->existingEmails[] = $row['email'];
            $this->existingNis[] = $row['nis'];


            // Insert user & siswa tanpa nested transaction
            $user = User::create([
                'name'      => $row['nama'],
                'email'     => $row['email'],
                'password'  => $this->defaultPassword,
                'roles'     => 'siswa',
            ]);

            Siswa::create([
                'user_id'    => $user->id,
                'nama'       => $row['nama'],
                'nis'        => $row['nis'],
                'kelas'      => $row['kelas'] ?? null,
                'jurusan'    => $row['jurusan'] ?? null,
                'no_hp'      => $row['no_hp'] ?? null,
                'alamat'     => $row['alamat'] ?? null,
                'foto_wajah' => $row['foto_wajah'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal import siswa: ' . $e->getMessage(), $row);
            throw $e;
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
