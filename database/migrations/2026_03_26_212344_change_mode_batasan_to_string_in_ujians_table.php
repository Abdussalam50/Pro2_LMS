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
        Schema::table('ujians', function (Blueprint $table) {
            $table->string('mode_batasan')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->enum('mode_batasan', ['strict', 'materi_only', 'open'])->change();
        });
    }
};
