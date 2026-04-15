<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class KunciJawaban extends Model
{
    use HasUuids;

    protected $table = 'kunci_jawaban';
    protected $primaryKey = 'kunci_jawaban_id';

    protected $fillable = [
        'soal_id',
        'kunci_jawaban',
        'tipe_soal',
        'share_kelas_id',
        'share_pertemuan_id',
    ];

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id', 'soal_id');
    }
}
