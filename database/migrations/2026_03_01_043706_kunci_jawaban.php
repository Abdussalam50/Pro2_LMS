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
        Schema::create('kunci_jawaban', function (Blueprint $table) {
            $table->uuid('kunci_jawaban_id')->primary();
            $table->foreignUuid('soal_id')->constrained('soal', 'soal_id')->onDelete('cascade');
            $table->longText('kunci_jawaban')->nullable();
            $table->enum('tipe_soal', ['pilihan_ganda', 'esai'])->default('pilihan_ganda');
            $table->uuid('share_kelas_id')->nullable();
            $table->uuid('share_pertemuan_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunci_jawaban');
    }
};
