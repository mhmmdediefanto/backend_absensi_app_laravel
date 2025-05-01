<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataGuru = [
            [
                'nama' => 'Mahmud S.Pd, M.Kom',
                'nip' => '1234567890',
                'no_hp' => '08123456789',
                'email' => 'mahmud@gmail.com',
                'password' => 'guru123'
            ],
            [
                'nama' => 'Ani S.Pd',
                'nip' => '9876543210',
                'no_hp' => '08234567890',
                'email' => 'ani@gmail.com',
                'password' => 'guru456'
            ]
        ];

        foreach ($dataGuru as $guru) {
            $user = User::create([
                'email' => $guru['email'],
                'password' => Hash::make($guru['password']),
                'roles' => 'guru'
            ]);

            Guru::create([
                'user_id' => $user->id,
                'nama' => $guru['nama'],
                'nip' => $guru['nip'],
                'no_hp' => $guru['no_hp']
            ]);
        }
    }
}
