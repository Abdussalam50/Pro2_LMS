<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->uuid('notifikasi_id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->enum('tipe', ['diskusi_kelompok', 'diskusi_dosen', 'tugas', 'pengumuman']);
            $table->json('data'); // lebih baik dari longText: queryable per field
            $table->boolean('dibaca')->default(false);
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();

            // Index untuk query notifikasi per user yang belum dibaca
            $table->index(['user_id', 'dibaca']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
