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
        Schema::create('anak_bimbings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pembimbing_id');
            $table->foreign('pembimbing_id')->references('id')->on('pembimbings')->onDelete('cascade');
            $table->unsignedBigInteger('siswa_id');
            $table->foreign('siswa_id')->references('id')->on('siswas')->onDelete('cascade');
            $table->enum('status', ['aktif', 'selsai', 'ditolak', 'dikeluarkan'])->default('aktif');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anak_bimbings');
    }
};
