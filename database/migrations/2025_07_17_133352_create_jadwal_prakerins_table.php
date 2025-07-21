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
        Schema::create('jadwal_prakerins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lokasi_prakerin_id')->constrained()->onDelete('cascade');
            $table->string('hari', 10);
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->integer('toleransi_masuk')->default(10);  // Default 10 menit toleransi
            $table->integer('toleransi_pulang')->default(0);  // Misal tidak boleh pulang cepat
            $table->boolean('status_aktif')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_prakerins');
    }
};
