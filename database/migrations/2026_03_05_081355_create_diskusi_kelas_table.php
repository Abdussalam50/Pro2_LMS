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
        Schema::create('diskusi_kelas', function (Blueprint $table) {
            $table->uuid('diskusi_kelas_id')->primary();
            $table->uuid('pertemuan_id');
            $table->unsignedBigInteger('user_id');
            $table->longText('pesan');
            $table->string('lampiran_url')->nullable();
            $table->timestamps();

            $table->foreign('pertemuan_id')->references('pertemuan_id')->on('pertemuans')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diskusi_kelas');
    }
};
