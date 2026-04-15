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
        // Copy existing kelas_id from mahasiswas into pivot table
        $mahasiswas = \Illuminate\Support\Facades\DB::table('mahasiswas')
            ->whereNotNull('kelas_id')
            ->get(['mahasiswa_id', 'kelas_id']);

        foreach ($mahasiswas as $m) {
            \Illuminate\Support\Facades\DB::table('kelas_mahasiswa')->insertOrIgnore([
                'mahasiswa_id' => $m->mahasiswa_id,
                'kelas_id'     => $m->kelas_id,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('kelas_mahasiswa')->truncate();
    }
};
