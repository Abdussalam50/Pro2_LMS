<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JawabanUjianMahasiswa extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'jawabans_ujian_mahasiswa';
    protected $primaryKey = 'jawaban_id';
    protected $guarded = [];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id', 'ujian_id');
    }

    public function soal()
    {
        return $this->belongsTo(SoalUjian::class, 'soal_id', 'soal_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(MahasiswaData::class, 'mahasiswa_id', 'mahasiswa_id');
    }
}
