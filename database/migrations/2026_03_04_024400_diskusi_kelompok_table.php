<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chat mahasiswa dalam konteks pertemuan + kelompok
        Schema::create('diskusi_kelompok', function (Blueprint $table) {
            $table->uuid('diskusi_kelompok_id')->primary();
            $table->uuid('pertemuan_id');
            $table->uuid('kelompok_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('pertemuan_id')->references('pertemuan_id')->on('pertemuans')->cascadeOnDelete();
            $table->foreign('kelompok_id')->references('kelompok_id')->on('kelompok')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->longText('pesan');
            $table->string('lampiran_url')->nullable();
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diskusi_kelompok');
    }
};
