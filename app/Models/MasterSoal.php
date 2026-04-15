<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MasterSoal extends Model
{
    use HasUuids;

    protected $table = 'master_soal';
    protected $primaryKey = 'master_soal_id';

    protected $fillable = [
        'master_soal',
        'bobot',
        'tahapan_sintaks_id',
        'is_diskusi',
        'is_show_jawaban',
        'is_show_master_soal',
        'is_shared',
        'tenggat_waktu',
        'bank_soal_id',
    ];

    protected $casts = [
        'is_diskusi' => 'boolean',
        'is_show_jawaban' => 'boolean',
        'is_show_kunci_jawaban' => 'boolean',
        'is_show_master_soal' => 'boolean',
        'is_shared' => 'boolean',
        'tenggat_waktu' => 'datetime',
    ];

    public function tahapanSintaks()
    {
        return $this->belongsTo(TahapanSintaks::class, 'tahapan_sintaks_id', 'tahapan_sintaks_id');
    }

    public function mainSoal()
    {
        return $this->hasMany(MainSoal::class, 'master_soal_id', 'master_soal_id');
    }

    public function gradingComponent()
    {
        return $this->belongsTo(GradingComponent::class, 'grading_component_id', 'id');
    }
}
