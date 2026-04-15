<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\DosenData;
class Pengumuman extends Model
{
    use HasUuids;

    protected $table = 'pengumuman';
    protected $primaryKey = 'pengumuman_id';
    protected $fillable = ['dosen_id', 'judul', 'konten'];

    public function dosen()
    {
        return $this->belongsTo(DosenData::class, 'dosen_id', 'dosen_id');
    }
}
