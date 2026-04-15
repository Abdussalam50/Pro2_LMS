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
        //
        Schema::create('jawabans_ujian_mahasiswa',function(Blueprint $table){
            $table->uuid('jawaban_id')->primary();
            $table->foreignUuid('ujian_id')->constrained('ujians', 'ujian_id')->cascadeOnDelete();
            $table->foreignUuid('soal_id')->constrained('soal_ujians', 'soal_id')->cascadeOnDelete();
            $table->foreignUuid('mahasiswa_id')->constrained('mahasiswas', 'mahasiswa_id')->cascadeOnDelete();
            $table->longText('jawaban_esai')->nullable();
            $table->string('jawaban_pilihan_ganda')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
