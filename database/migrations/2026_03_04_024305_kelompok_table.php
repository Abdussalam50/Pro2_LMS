<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel utama kelompok
        Schema::create('kelompok', function (Blueprint $table) {
            $table->uuid('kelompok_id')->primary();
            $table->uuid('kelas_id');
            $table->foreign('kelas_id')->references('kelas_id')->on('kelas')->cascadeOnDelete();
            $table->string('nama_kelompok');
            $table->timestamps();
        });

        // Tabel pivot anggota kelompok (many-to-many: kelompok <-> users)
        Schema::create('kelompok_anggota', function (Blueprint $table) {
            $table->uuid('kelompok_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('kelompok_id')->references('kelompok_id')->on('kelompok')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->enum('peran', ['ketua', 'anggota'])->default('anggota');
            $table->timestamps();

            $table->primary(['kelompok_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelompok_anggota');
        Schema::dropIfExists('kelompok');
    }
};
