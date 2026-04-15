<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DiskusiDosen extends Model
{
    use HasUuids;

    protected $table = 'diskusi_dosen';
    protected $primaryKey = 'diskusi_dosen_id';
    protected $fillable = ['pertemuan_id', 'kelompok_id', 'user_id', 'pesan', 'lampiran_url', 'tahapan_sintaks_id'];

    public function user()      { return $this->belongsTo(User::class, 'user_id', 'id'); }
    public function kelompok()  { return $this->belongsTo(Kelompok::class, 'kelompok_id', 'kelompok_id'); }
    public function pertemuan() { return $this->belongsTo(Pertemuan::class, 'pertemuan_id', 'pertemuan_id'); }
}
