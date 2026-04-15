<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GradingComponent extends Model
{
    use HasUuids;

    protected $table = 'grading_components';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kelas_id',
        'category', // legacy
        'name',
        'weight',
        'is_default',
        'mapping_type', // assignment, exam, attendance, manual
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'kelas_id');
    }

    public function masterSoals()
    {
        return $this->hasMany(MasterSoal::class, 'grading_component_id', 'id');
    }

    public function ujians()
    {
        return $this->hasMany(Ujian::class, 'grading_component_id', 'id');
    }
}
