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
        Schema::create('external_materi',function(Blueprint $table){
            $table->uuid('external_materi_id')->primary();
            $table->string('judul');
            $table->string('link');
            $table->string('deskripsi');
            $table->foreignUuid('mata_kuliah_id')->constrained('mata_kuliah', 'mata_kuliah_id')->cascadeOnDelete();
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
