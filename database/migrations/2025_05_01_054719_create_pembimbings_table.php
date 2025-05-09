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
        Schema::create('pembimbings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('guru_id');
            $table->foreign('guru_id')->references('id')->on('gurus')->onDelete('cascade');
            $table->unsignedBigInteger('lokasi_prakerin_id');
            $table->foreign('lokasi_prakerin_id')->references('id')->on('lokasi_prakerins')->onDelete('cascade');
            $table->timestamps();

            // Tambahan constraint untuk menjaga logika relasi
            $table->unique('user_id');   // 1 user hanya punya 1 pembimbing
            $table->unique('guru_id');   // 1 guru hanya membimbing 1 lokasi prakerin
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembimbings');
    }
};
