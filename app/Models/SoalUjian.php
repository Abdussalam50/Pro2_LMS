<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SoalUjian extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'soal_ujians';
    protected $primaryKey = 'soal_id';
    protected $guarded = [];

    protected $casts = [
        'pilihan_ganda' => 'json',
    ];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id', 'ujian_id');
    }

    public function jawabans()
    {
        return $this->hasMany(JawabanUjianMahasiswa::class, 'soal_id', 'soal_id');
    }
}
