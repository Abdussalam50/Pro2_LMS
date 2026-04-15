<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DiskusiKelas extends Model
{
    use HasUuids;

    protected $table = 'diskusi_kelas';
    protected $primaryKey = 'diskusi_kelas_id';
    protected $fillable = ['pertemuan_id', 'user_id', 'pesan', 'lampiran_url', 'tahapan_sintaks_id'];

    public function user()      { return $this->belongsTo(User::class, 'user_id', 'id'); }
    public function pertemuan() { return $this->belongsTo(Pertemuan::class, 'pertemuan_id', 'pertemuan_id'); }
}
