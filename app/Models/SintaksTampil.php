<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SintaksTampil extends Model
{
    use HasUuids;

    protected $table = 'sintaks_tampil';
    protected $primaryKey = 'sintaks_tampil_id';

    protected $fillable = [
        'kelas_id',
        'pertemuan_id',
        'sintaks_belajar_id',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'kelas_id');
    }

    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id', 'pertemuan_id');
    }

    public function sintaksBelajar()
    {
        return $this->belongsTo(SintaksBelajar::class, 'sintaks_belajar_id', 'sintaks_belajar_id');
    }
}
