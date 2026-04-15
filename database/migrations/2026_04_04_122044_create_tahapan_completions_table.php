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
        Schema::create('tahapan_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->uuid('tahapan_sintaks_id');
            $table->uuid('pertemuan_id');
            $table->string('status')->default('completed');
            $table->timestamps();

            // Indexing for faster lookups
            $table->index(['user_id', 'pertemuan_id']);
            $table->index(['user_id', 'tahapan_sintaks_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahapan_completions');
    }
};
