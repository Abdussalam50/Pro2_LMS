<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ExternalMateri extends Model
{
    use HasUuids;

    protected $table = 'external_materi';
    protected $primaryKey = 'external_materi_id';
    protected $guarded = [];

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id', 'mata_kuliah_id');
    }
}
