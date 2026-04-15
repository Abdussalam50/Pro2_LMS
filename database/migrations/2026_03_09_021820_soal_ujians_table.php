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
        Schema::create('soal_ujians',function(Blueprint $table){
            $table->uuid('soal_id')->primary();
            $table->foreignUuid('ujian_id')->constrained('ujians', 'ujian_id')->cascadeOnDelete();
            $table->longText('soal');
            $table->json('pilihan_ganda')->nullable();
            $table->text('jawaban_esai')->nullable();
            $table->string('jawaban_benar')->nullable();
            $table->integer('bobot')->default(1);
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
