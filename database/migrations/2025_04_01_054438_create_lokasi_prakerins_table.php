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
        Schema::create('lokasi_prakerins', function (Blueprint $table) {
            $table->id();
            $table->string('nama_instansi', 255);
            $table->string('alamat');
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->string('kontak_person')->nullable();
            $table->string('no_telp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('bidang_usaha')->nullable();
            $table->integer('kapasitas_siswa')->nullable();
            $table->boolean('status_kerjasama')->default(true);
            $table->date('mulai_kerjasama')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasi_prakerins');
    }
};
