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
        Schema::create('bank_soals', function (Blueprint $table) {
            $table->uuid('bank_soal_id')->primary();
            $table->uuid('dosen_id');
            $table->enum('jenis', ['ujian', 'tugas'])->index();
            $table->string('tipe_soal')->nullable(); // pilihan_ganda, esai, kompleks (untuk tugas/sintaks JSON)
            $table->string('judul_soal')->nullable();
            $table->longText('konten_soal')->nullable(); 
            $table->json('opsi_jawaban')->nullable();
            $table->text('kunci_jawaban')->nullable();
            $table->integer('bobot_referensi')->default(1);
            $table->timestamps();

            $table->foreign('dosen_id')->references('dosen_id')->on('dosens')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_soals');
    }
};
