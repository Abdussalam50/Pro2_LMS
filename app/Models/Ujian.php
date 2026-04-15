<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ujian extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'ujians';
    protected $primaryKey = 'ujian_id';
    protected $guarded = [];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'is_active' => 'boolean',
        'is_open' => 'boolean',
        'is_open_materi' => 'boolean',
        'is_random' => 'boolean',
        'custom_settings' => 'array',
    ];

    public function getHandler(): \App\Contracts\UjianHandlerInterface
    {
        $handlerClass = $this->custom_handler ?? \App\Services\Ujian\BaseUjianHandler::class;
        
        if (class_exists($handlerClass)) {
            return new $handlerClass();
        }

        return new \App\Services\Ujian\BaseUjianHandler();
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id', 'mata_kuliah_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'kelas_id');
    }

    public function dosen()
    {
        return $this->belongsTo(DosenData::class, 'dosen_id', 'dosen_id');
    }

    public function soalUjians()
    {
        return $this->hasMany(SoalUjian::class, 'ujian_id', 'ujian_id');
    }

    public function materiUjians()
    {
        return $this->hasMany(MateriUjian::class, 'ujian_id', 'ujian_id');
    }

    public function gradingComponent()
    {
        return $this->belongsTo(GradingComponent::class, 'grading_component_id', 'id');
    }
}
