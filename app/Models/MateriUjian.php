<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MateriUjian extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'materi_ujians';
    protected $primaryKey = 'materi_id';
    protected $guarded = [];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id', 'ujian_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'kelas_id');
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id', 'mata_kuliah_id');
    }

    public function dosen()
    {
        return $this->belongsTo(DosenData::class, 'dosen_id', 'dosen_id');
    }
}
