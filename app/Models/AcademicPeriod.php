<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AcademicPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tahun',
        'semester',
        'weight_task',
        'weight_mid',
        'weight_final',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }

    public function classes()
    {
        return $this->hasMany(Kelas::class, 'academic_period_id');
    }
}
