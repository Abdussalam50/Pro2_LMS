<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PilihanGanda extends Model
{
    use HasUuids;

    protected $table = 'pilihan_ganda';
    protected $primaryKey = 'pilihan_ganda_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'soal_id',
        'pilihan_ganda',
        'status',
    ];

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id');
    }
}
