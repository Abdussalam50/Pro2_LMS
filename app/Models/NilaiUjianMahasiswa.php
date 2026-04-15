<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NilaiUjianMahasiswa extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'nilai_ujians_mahasiswa';
    protected $primaryKey = 'nilai_id';
    protected $guarded = [];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id', 'ujian_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(MahasiswaData::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    public function dosen()
    {
        return $this->belongsTo(DosenData::class, 'dosen_id', 'dosen_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'kelas_id');
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id', 'mata_kuliah_id');
    }
}
