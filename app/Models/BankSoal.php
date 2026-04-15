<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BankSoal extends Model
{
    use HasFactory;

    protected $table = 'bank_soals';
    protected $primaryKey = 'bank_soal_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'dosen_id',
        'jenis',            // 'ujian' atau 'tugas'
        'tipe_soal',        // 'pilihan_ganda', 'esai', atau 'kompleks'
        'judul_soal',       // Opsional untuk referensi dosen
        'konten_soal',      // Longtext untuk narasi/pertanyaan
        'opsi_jawaban',     // JSON untuk pilihan ganda/kompleks
        'kunci_jawaban',    // Kunci jawaban (string atau teks)
        'bobot_referensi',  // Bobot nilai default
    ];

    protected $casts = [
        'opsi_jawaban' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function dosen()
    {
        return $this->belongsTo(DosenData::class, 'dosen_id', 'dosen_id');
    }
}
