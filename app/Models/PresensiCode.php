<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PresensiCode extends Model
{
    use HasUuids;

    protected $table = 'presensi_codes';
    protected $primaryKey = 'presensi_code_id';

    protected $fillable = [
        'pertemuan_id',
        'type',
        'code',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id', 'pertemuan_id');
    }
}
