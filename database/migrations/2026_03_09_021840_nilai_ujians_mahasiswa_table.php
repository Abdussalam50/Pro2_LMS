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
        Schema::create('nilai_ujians_mahasiswa',function(Blueprint $table){
            $table->uuid('nilai_id')->primary();
            $table->foreignUuid('ujian_id')->constrained('ujians', 'ujian_id')->cascadeOnDelete();
            $table->foreignUuid('kelas_id')->constrained('kelas', 'kelas_id')->cascadeOnDelete();
            $table->foreignUuid('mata_kuliah_id')->constrained('mata_kuliah', 'mata_kuliah_id')->cascadeOnDelete();
            $table->foreignUuid('dosen_id')->constrained('dosens', 'dosen_id')->cascadeOnDelete();
            $table->foreignUuid('mahasiswa_id')->constrained('mahasiswas', 'mahasiswa_id')->cascadeOnDelete();
            $table->integer('nilai')->nullable();
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
