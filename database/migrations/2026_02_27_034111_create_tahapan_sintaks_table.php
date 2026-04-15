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
        Schema::create('tahapan_sintaks', function (Blueprint $table) {
            $table->uuid('tahapan_sintaks_id')->primary();
            $table->foreignUuid('sintaks_belajar_id')->constrained('sintaks_belajar', 'sintaks_belajar_id')->onDelete('cascade');
            $table->string('nama_tahapan');
            $table->integer('urutan')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahapan_sintaks');
    }
};
