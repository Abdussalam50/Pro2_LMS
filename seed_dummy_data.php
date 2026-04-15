<?php

use App\Models\User;
use App\Models\MataKuliah;
use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\SintaksBelajar;
use App\Models\Kegiatan;
use App\Models\MasterSoal;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;

$dosenUser = User::where('email', 'dosen@lms.com')->first();
if (!$dosenUser) die('Dosen user not found');
$dosen = DB::table('dosens')->where('user_id', $dosenUser->id)->first();
if (!$dosen) die('Dosen record not found');

// Clear existing items
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
MasterSoal::truncate();
Kegiatan::truncate();
SintaksBelajar::truncate();
Pertemuan::truncate();
Kelas::truncate();
MataKuliah::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

// Create MataKuliah
$matkul = MataKuliah::create([
    'mata_kuliah' => 'Pemrograman Web Advanced',
    'kode' => 'PWA-IF201',
    'dosen_id' => $dosen->dosen_id
]);

// Create Kelas
$kelas = Kelas::create([
    'kelas' => 'Kelas A Pagi',
    'kode' => 'PWA-A',
    'mata_kuliah_id' => $matkul->mata_kuliah_id
]);

// Create Pertemuan
$pertemuan = Pertemuan::create([
    'pertemuan' => 'Pertemuan 1: Semantic HTML & Accessibility',
    'kelas_id' => $kelas->kelas_id
]);

// Create SintaksBelajar
$sintaks = SintaksBelajar::create([
    'sintaks_belajar' => 'PBL: Membuat Landing Page Accessible',
    'pertemuan_id' => $pertemuan->pertemuan_id,
    'model_pembelajaran' => 'pbl'
]);

// Create Kegiatan
Kegiatan::create([
    'kegiatan' => '1. Orientasi siswa pada masalah (Menganalisa web tidak ramah tunanetra)',
    'sintaks_belajar_id' => $sintaks->sintaks_belajar_id
]);
Kegiatan::create([
    'kegiatan' => '2. Mengorganisasi siswa dalam kelompok kecil (3 orang)',
    'sintaks_belajar_id' => $sintaks->sintaks_belajar_id
]);
Kegiatan::create([
    'kegiatan' => '3. Membimbing penyelidikan tools accessibility lighthouse',
    'sintaks_belajar_id' => $sintaks->sintaks_belajar_id
]);

// Create MasterSoal
MasterSoal::create([
    'master_soal' => 'Kuis Aksesibilitas Web',
    'sintaks_belajar_id' => $sintaks->sintaks_belajar_id,
    'is_diskusi' => true,
    'is_show_jawaban' => false,
    'is_show_kunci_jawaban' => false,
    'is_show_master_soal' => true,
    'is_shared' => true
]);

echo "SUCCESS_CLASS_ID=" . $kelas->kelas_id . "\n";
