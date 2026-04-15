<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollmentRequest extends Model
{
    protected $fillable = ['mahasiswa_id', 'kelas_id', 'status'];

    public function mahasiswa()
    {
        return $this->belongsTo(MahasiswaData::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'kelas_id');
    }
}
