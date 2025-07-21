<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GuruImport implements ToModel, WithHeadingRow
{
    protected $existingEmails = [];
    protected $existingNip = [];
    protected $defaultPasswordHash;

    public function __construct()
    {
        // Ambil semua email & nip yang sudah ada di DB
        $this->existingEmails = User::pluck('email')->toArray();
        $this->existingNip = Guru::pluck('nip')->toArray();

        // Hash password default sekali saja (misal: guru123)
        $this->defaultPasswordHash = Hash::make('guruSkahada123');
    }

    public function model(array $row)
    {
        if (empty($row['email']) || empty($row['nip']) || empty($row['nama'])) {
            throw new \Exception('Wajib isi kolom: email / nip / nama');
        }

        if (in_array($row['email'], $this->existingEmails)) {
            throw new \Exception('Email sudah ada di database: ' . $row['email']);
        }

        if (in_array($row['nip'], $this->existingNip)) {
            throw new \Exception('NIP sudah ada di database: ' . $row['nip']);
        }

        // Setelah lolos validasi, masukkan ke array supaya dicek di row berikutnya
        $this->existingEmails[] = $row['email'];
        $this->existingNip[] = $row['nip'];

        $user = User::create([
            'name'     => $row['nama'],
            'email'    => $row['email'],
            'password' => $this->defaultPasswordHash, // Pakai hash default
            'roles'    => 'guru',
        ]);

        Guru::create([
            'user_id' => $user->id,
            'nama'    => $row['nama'],
            'nip'     => $row['nip'],
            'no_hp'   => $row['no_hp'] ?? null,
            'jabatan' => $row['jabatan'] ?? null,
            'foto'    => $row['foto'] ?? null,
        ]);

        return $user;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
