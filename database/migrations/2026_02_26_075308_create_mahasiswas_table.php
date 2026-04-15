<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up():void{
    Schema::create('mahasiswas',function(Blueprint $table){
        $table->uuid('mahasiswa_id')->primary();
        $table->string('nama');
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->string('nim')->unique();
        $table->string('angkatan');
        $table->string('program_studi');
        $table->string('no_wa');
        $table->string('foto');
        $table->boolean('is_active');
        $table->string('kode_verifikasi');
        $table->string('kelas_id')->nullable();
        $table->timestamps();
    
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswas');
    }
};
