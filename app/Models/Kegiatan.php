<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Kegiatan extends Model
{
    use HasUuids;

    protected $table = 'kegiatan';
    protected $primaryKey = 'kegiatan_id';

    protected $fillable = [
        'kegiatan',
        'tahapan_sintaks_id',
    ];

    public function tahapanSintaks()
    {
        return $this->belongsTo(TahapanSintaks::class, 'tahapan_sintaks_id', 'tahapan_sintaks_id');
    }
}
