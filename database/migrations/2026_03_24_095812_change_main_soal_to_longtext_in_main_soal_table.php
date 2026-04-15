<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('main_soal', function (Blueprint $table) {
        $table->longText('main_soal')->change();
    });
}

public function down(): void
{
    Schema::table('main_soal', function (Blueprint $table) {
        $table->string('main_soal')->change();
    });
}




};
