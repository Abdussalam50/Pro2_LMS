<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SintaksBelajar extends Model
{
    use HasUuids;

    protected $table = 'sintaks_belajar';
    protected $primaryKey = 'sintaks_belajar_id';
    
    protected $fillable = [
        'sintaks_belajar',
        'pertemuan_id',
        'model_pembelajaran',
    ];

    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id', 'pertemuan_id');
    }

    public function tahapanSintaks()
    {
        return $this->hasMany(TahapanSintaks::class, 'sintaks_belajar_id', 'sintaks_belajar_id')->orderBy('urutan');
    }
}
