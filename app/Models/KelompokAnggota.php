<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class KelompokAnggota extends Model
{
    protected $table = 'kelompok_anggota';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = ['kelompok_id', 'user_id', 'peran'];

    public function kelompok()
    {
        return $this->belongsTo(Kelompok::class, 'kelompok_id', 'kelompok_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
