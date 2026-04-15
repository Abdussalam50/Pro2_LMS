<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Kelompok extends Model
{
    use HasUuids;

    protected $table = 'kelompok';
    protected $primaryKey = 'kelompok_id';
    protected $fillable = ['kelas_id', 'nama_kelompok'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'kelas_id');
    }

    public function anggota()
    {
        return $this->hasMany(KelompokAnggota::class, 'kelompok_id', 'kelompok_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'kelompok_anggota', 'kelompok_id', 'user_id')
            ->withPivot('peran')
            ->withTimestamps();
    }

    public function diskusiKelompok()
    {
        return $this->hasMany(DiskusiKelompok::class, 'kelompok_id', 'kelompok_id');
    }

    public function diskusiDosen()
    {
        return $this->hasMany(DiskusiDosen::class, 'kelompok_id', 'kelompok_id');
    }
}
