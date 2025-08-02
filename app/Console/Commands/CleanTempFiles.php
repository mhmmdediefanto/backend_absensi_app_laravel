<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanTempFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temp:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus file temp yang lebih dari 1 hari';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $files = Storage::disk('public')->files('temp');
        $this->info("Total file temp: " . count($files));
        $count = 0;

        foreach ($files as $file) {
            Storage::disk('public')->delete($file);
            $count++;
        }

        $this->info("Total file dihapus: {$count}");
    }
}
