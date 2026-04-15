<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PresensiMahasiswa extends Model
{
    use HasUuids;

    protected $table = 'presensi_mahasiswa';
    protected $primaryKey = 'presensi_id';

    protected $fillable = [
        'pertemuan_id',
        'mahasiswa_id',
        'status',
        'waktu_presensi',
        'metode',
        'catatan',
    ];

    protected $casts = [
        'waktu_presensi' => 'datetime',
    ];

    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id', 'pertemuan_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(MahasiswaData::class, 'mahasiswa_id', 'mahasiswa_id');
    }
}
