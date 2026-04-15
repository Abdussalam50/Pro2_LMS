<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class JawabanMahasiswa extends Model
{
    use HasUuids;

    protected $table = 'jawaban_mahasiswa';
    protected $primaryKey = 'jawaban_id';
    protected $fillable = ['master_soal_id', 'soal_id', 'user_id', 'jawaban', 'pilihan', 'nilai', 'catatan', 'is_submitted'];

    public function soal()       { return $this->belongsTo(Soal::class, 'soal_id', 'soal_id'); }
    public function masterSoal() { return $this->belongsTo(MasterSoal::class, 'master_soal_id', 'master_soal_id'); }
    public function user()       { return $this->belongsTo(User::class, 'user_id', 'id'); }
}
