<?php

namespace App\Console\Commands;

use App\Imports\SiswaImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportSiswaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:siswa {file=storage/app/public/import/users_siswa_250_dummy.csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data siswa dari file excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error('File tidak ditemukan: ' . $file);
            return;
        }

        $this->info('Mulai import...');

        $start = microtime(true);

        Excel::import(new SiswaImport, $file);

        $end = microtime(true);

        $this->info('Import selesai dalam ' . round($end - $start, 2) . ' detik.');
    }
}
