<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class NotifikasiTerjadwal extends Model
{
    use HasUuids;

    protected $table = 'notifikasi_terjadwal';
    protected $fillable = [
        'dosen_id',
        'kelas_id',
        'judul',
        'isi',
        'waktu_kirim',
        'perulangan',
        'status',
        'terakhir_dikirim'
    ];

    protected $casts = [
        'waktu_kirim' => 'datetime',
        'terakhir_dikirim' => 'datetime',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'kelas_id');
    }
}
