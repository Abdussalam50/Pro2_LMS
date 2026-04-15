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
    Schema::create('dosens', function (Blueprint $table) {
        // Gunakan 'id' saja agar Filament langsung mengenali secara otomatis
        $table->uuid('dosen_id')->primary(); 
        
        $table->string('nama');
        
        // Relasi ke tabel users
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        
        $table->string('kode')->unique();
        $table->string('no_wa');
        
        // Tambahkan nullable() agar tidak error jika saat daftar belum ada foto
        $table->string('foto')->nullable(); 
        
        // Cukup panggil satu kali tanpa parameter
        $table->timestamps(); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosens');
    }
};
