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
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('lokasi_prakerin_id')->nullable();
            $table->foreign('lokasi_prakerin_id')->references('id')->on('lokasi_prakerins')->onDelete('set null');

            $table->date('tanggal')->nullable();
            $table->time('time')->nullable();
            $table->enum('tipe_absen', ['masuk', 'pulang'])->nullable();
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->enum('status_lokasi', ['valid', 'invalid'])->nullable();
            $table->enum('status_wajah', ['valid', 'invalid'])->nullable();
            $table->text('foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
