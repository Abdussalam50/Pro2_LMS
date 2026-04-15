<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Pertemuan extends Model
{
    use HasUuids;

    protected $table = 'pertemuans';
    protected $primaryKey = 'pertemuan_id';
    protected $guarded = [];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'kelas_id');
    }

    public function sintaksBelajar()
    {
        return $this->hasOne(SintaksBelajar::class, 'pertemuan_id', 'pertemuan_id');
    }

    public function diskusiKelas()
    {
        return $this->hasMany(DiskusiKelas::class, 'pertemuan_id', 'pertemuan_id');
    }

    public function diskusiKelompok()
    {
        return $this->hasMany(DiskusiKelompok::class, 'pertemuan_id', 'pertemuan_id');
    }

    public function diskusiDosen()
    {
        return $this->hasMany(DiskusiDosen::class, 'pertemuan_id', 'pertemuan_id');
    }
}
