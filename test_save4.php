<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$pertemuanId = Str::uuid()->toString();
$sintaksBelajarId = Str::uuid()->toString();

DB::statement('SET FOREIGN_KEY_CHECKS=0;');

DB::table('pertemuans')->insert([
    'pertemuan_id' => $pertemuanId,
    'pertemuan' => 'Test Meeting',
    'kelas_id' => Str::uuid()->toString()
]);

DB::table('sintaks_belajar')->insert([
    'sintaks_belajar_id' => $sintaksBelajarId,
    'sintaks_belajar' => 'PBL',
    'pertemuan_id' => $pertemuanId,
    'model_pembelajaran' => 'pbl'
]);

echo "Inserted SintaksBelajar...\n";

DB::table('materis')->insert([
    'sintaks_belajar_id' => $sintaksBelajarId,
    'judul' => 'Test',
    'isi_materi' => 'Test content'
]);

echo "Inserted Materi...\n";

$masterSoalId = Str::uuid()->toString();
DB::table('master_soal')->insert([
    'master_soal_id' => $masterSoalId,
    'master_soal' => 'Quiz 1',
    'sintaks_belajar_id' => $sintaksBelajarId
]);

echo "Inserted MasterSoal...\n";

$mainSoalId = Str::uuid()->toString();
DB::table('main_soal')->insert([
    'main_soal_id' => $mainSoalId,
    'master_soal_id' => $masterSoalId,
    'main_soal' => 'Main Tugas'
]);

DB::table('soal')->insert([
    'soal_id' => Str::uuid()->toString(),
    'soal' => 'Answer this',
    'bobot' => 100,
    'main_soal_id' => $mainSoalId
]);

echo "Inserted Soal successfully.\n";

DB::statement('SET FOREIGN_KEY_CHECKS=1;');
