<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_mahasiswa', function (Blueprint $table) {
            $table->uuid('jawaban_id')->primary();
            $table->uuid('master_soal_id');
            $table->uuid('soal_id');
            $table->unsignedBigInteger('user_id');
            $table->text('jawaban')->nullable();          // Jawaban essay
            $table->string('pilihan')->nullable();         // Jawaban pilihan ganda
            $table->unsignedTinyInteger('nilai')->nullable(); // 0-100
            $table->foreign('master_soal_id')->references('master_soal_id')->on('master_soal')->cascadeOnDelete();
            $table->foreign('soal_id')->references('soal_id')->on('soal')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['soal_id', 'user_id']); // 1 jawaban per soal per mahasiswa
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_mahasiswa');
    }
};
