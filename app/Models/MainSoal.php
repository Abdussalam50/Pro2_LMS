<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MainSoal extends Model
{
    use HasUuids;

    protected $table = 'main_soal';
    protected $primaryKey = 'main_soal_id';

    protected $fillable = [
        'main_soal',
        'master_soal_id',
    ];

    public function masterSoal()
    {
        return $this->belongsTo(MasterSoal::class, 'master_soal_id', 'master_soal_id');
    }

    public function soal()
    {
        return $this->hasMany(Soal::class, 'main_soal_id', 'main_soal_id');
    }
}
