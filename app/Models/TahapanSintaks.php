<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TahapanSintaks extends Model
{
    use HasUuids;

    protected $table = 'tahapan_sintaks';
    protected $primaryKey = 'tahapan_sintaks_id';

    protected $fillable = [
        'sintaks_belajar_id',
        'nama_tahapan',
        'urutan',
    ];

    public function sintaksBelajar()
    {
        return $this->belongsTo(SintaksBelajar::class, 'sintaks_belajar_id', 'sintaks_belajar_id');
    }

    public function kegiatan()
    {
        return $this->hasMany(Kegiatan::class, 'tahapan_sintaks_id', 'tahapan_sintaks_id');
    }

    public function materis()
    {
        return $this->hasMany(Materi::class, 'tahapan_sintaks_id', 'tahapan_sintaks_id');
    }

    public function masterSoal()
    {
        return $this->hasMany(MasterSoal::class, 'tahapan_sintaks_id', 'tahapan_sintaks_id');
    }
}
