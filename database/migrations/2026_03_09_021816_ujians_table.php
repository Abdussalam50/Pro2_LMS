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
        Schema::create('ujians',function(Blueprint $table){
            $table->uuid('ujian_id')->primary();
            $table->foreignUuid('mata_kuliah_id')->constrained('mata_kuliah', 'mata_kuliah_id')->cascadeOnDelete();
            $table->foreignUuid('kelas_id')->constrained('kelas', 'kelas_id')->cascadeOnDelete();
            $table->foreignUuid('dosen_id')->constrained('dosens', 'dosen_id')->cascadeOnDelete();
            $table->string('nama_ujian');
            $table->text('deskripsi')->nullable();
            $table->enum('jenis_ujian',['uts','uas']);
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai');
            $table->integer('jumlah_soal');
            $table->integer('bobot_nilai')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_open')->default(false);
            $table->boolean('is_open_materi')->default(false);
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
