<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TahapanSintaks;

class Materi extends Model
{
    protected $fillable = ['tahapan_sintaks_id', 'judul', 'isi_materi'];

    /**
     * Get the tahapan sintaks that owns the materi.
     */
    public function tahapanSintaks()
    {
        return $this->belongsTo(TahapanSintaks::class, 'tahapan_sintaks_id', 'tahapan_sintaks_id');
    }
}
