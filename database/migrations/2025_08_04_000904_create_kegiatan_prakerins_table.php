<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kegiatan_prakerins', function (Blueprint $table) {
            $table->id();
            // relasi ke siswa
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->string('judul');
            $table->longText('deskripsi')->nullable();
            $table->date('tanggal')->nullable();
            $table->text('catatan_guru')->nullable();

            $table->unique(['siswa_id', 'tanggal']);
            $table->timestamps();
        });

    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_prakerins');
    }
};
