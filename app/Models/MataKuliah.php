<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MataKuliah extends Model
{
    use HasUuids;

    protected $table = 'mata_kuliah';
    protected $primaryKey = 'mata_kuliah_id';
    protected $guarded = [];

    public function dosen()
    {
        return $this->belongsTo(DosenData::class, 'dosen_id', 'dosen_id');
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'mata_kuliah_id', 'mata_kuliah_id');
    }

    public function ujians()
    {
        return $this->hasMany(Ujian::class, 'mata_kuliah_id', 'mata_kuliah_id');
    }
}
