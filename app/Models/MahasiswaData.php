<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahasiswaData extends Model
{
    //
    protected $table='mahasiswas';
    protected $primaryKey='mahasiswa_id';
    protected $keyType='string';
    public $incrementing=false;
    protected $fillable=[
        'mahasiswa_id',
        'nama',
        'user_id',
        'nim',
        'angkatan',
        'program_studi',
        'no_wa',
        'foto',
        'is_active',
        'kode_verifikasi',
        'kelas_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function kelass()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_mahasiswa', 'mahasiswa_id', 'kelas_id')
                    ->withTimestamps();
    }

    /**
     * Find the class the student is enrolled in for a specific course (mata_kuliah_id).
     */
    public function enrolledClassForCourse(string $mataKuliahId)
    {
        return $this->kelass()->whereHas('mataKuliah', fn($q) => $q->where('mata_kuliah_id', $mataKuliahId))->first();
    }

    /**
     * Check if student is enrolled in a given class.
     */
    public function isEnrolledIn(string $kelasId): bool
    {
        return $this->kelass()->where('kelas_mahasiswa.kelas_id', $kelasId)->exists();
    }

    public function jawabans()
    {
        return $this->hasMany(JawabanUjianMahasiswa::class, 'mahasiswa_id', 'mahasiswa_id');
    }
}
