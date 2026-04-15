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
        Schema::create('sintaks_belajar', function (Blueprint $table) {
            $table->uuid('sintaks_belajar_id')->primary();
            $table->string('sintaks_belajar');
            $table->foreignUuid('pertemuan_id')->constrained('pertemuans', 'pertemuan_id')->onDelete('cascade');
            $table->string('model_pembelajaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sintaks_belajar');
    }
};
