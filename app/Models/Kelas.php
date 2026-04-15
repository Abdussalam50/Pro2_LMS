<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Kelas extends Model
{
    use HasUuids;

    protected $table = 'kelas';
    protected $primaryKey = 'kelas_id';
    protected $guarded = [];

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id', 'mata_kuliah_id');
    }

    public function mahasiswas()
    {
        return $this->belongsToMany(MahasiswaData::class, 'kelas_mahasiswa', 'kelas_id', 'mahasiswa_id')
                    ->withTimestamps();
    }

    public function ujians()
    {
        return $this->hasMany(Ujian::class, 'kelas_id', 'kelas_id');
    }

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }
}
