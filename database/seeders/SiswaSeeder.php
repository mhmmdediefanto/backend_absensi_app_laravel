<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataSiswa = [
            [
                'nama' => 'Muhammad Rizky',
                'email' => 'rizky@gmailcom',
                'password' => 'rizky123',
                'nis' => '2123300001',
                'kelas' => 'XII RPL 1',
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'no_hp' => '08123456789',
                'alamat' => 'Jl. Raya No. 1',
                'foto_wajah' => null,
                'lokasi_prakerin_id' => null
            ],
            [
                'nama' => 'Ani',
                'email' => 'ani@gmailcom',
                'password' => 'ani123',
                'nis' => '2123300002',
                'kelas' => 'XII RPL 1',
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'no_hp' => '08234567890',
                'alamat' => 'Jl. Raya No. 2',
                'foto_wajah' => null,
                'lokasi_prakerin_id' => null
            ],
            [
                'nama' => 'Budi',
                'email' => 'budi@gmailcom',
                'password' => 'budi123',
                'nis' => '2123300003',
                'kelas' => 'XII RPL 1',
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'no_hp' => '08345678901',
                'alamat' => 'Jl. Raya No. 3',
                'foto_wajah' => null,
                'lokasi_prakerin_id' => null
            ],
            [
                'nama' => 'Caca',
                'password' => 'caca123',
                'email' => 'caca@gmailcom',
                'nis' => '2123300004',
                'kelas' => 'XII RPL 1',
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'no_hp' => '08456789012',
                'alamat' => 'Jl. Raya No. 4',
                'foto_wajah' => null,
                'lokasi_prakerin_id' => null
            ],
            [
                'nama' => 'Dedi',
                'password' => 'dedi123',
                'email' => 'dedi@gmailcom',
                'nis' => '2123300005',
                'kelas' => 'XII RPL 1',
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'no_hp' => '08567890123',
                'alamat' => 'Jl. Raya No. 5',
                'foto_wajah' => null,
                'lokasi_prakerin_id' => null
            ],
            [
                'nama' => 'Eka',
                'password' => 'eka123',
                'email' => 'eka@gmailcom',
                'nis' => '2123300006',
                'kelas' => 'XII RPL 1',
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'no_hp' => '08678901234',
                'alamat' => 'Jl. Raya No. 6',
                'foto_wajah' => null,
                'lokasi_prakerin_id' => null
            ],
            [
                'nama' => 'Feri',
                'password' => 'feri123',
                'email' => 'feri@gmailcom',
                'nis' => '2123300007',
                'kelas' => 'XII RPL 1',
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'no_hp' => '08789012345',
                'alamat' => 'Jl. Raya No. 7',
                'foto_wajah' => null,
                'lokasi_prakerin_id' => null
            ]
        ];

        foreach ($dataSiswa as $siswa) {
            $user = User::create([
                'email' => $siswa['email'],
                'password' => Hash::make($siswa['password']),
                'roles' => 'siswa'
            ]);

            DB::table('siswas')->insert([
                'user_id' => $user->id,
                'nama' => $siswa['nama'],
                'nis' => $siswa['nis'],
                'kelas' => $siswa['kelas'],
                'jurusan' => $siswa['jurusan'],
                'no_hp' => $siswa['no_hp'],
                'alamat' => $siswa['alamat'],
                'foto_wajah' => $siswa['foto_wajah'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
