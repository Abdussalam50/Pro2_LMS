<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Soal extends Model
{
    use HasUuids;

    protected $table = 'soal';
    protected $primaryKey = 'soal_id';

    protected $fillable = [
        'soal',
        'main_soal_id',
        'bobot',
    ];

    public function mainSoal()
    {
        return $this->belongsTo(MainSoal::class, 'main_soal_id', 'main_soal_id');
    }

    public function kunciJawaban()
    {
        return $this->hasOne(KunciJawaban::class, 'soal_id', 'soal_id');
    }

    public function pilihanGanda()
    {
        return $this->hasMany(PilihanGanda::class, 'soal_id', 'soal_id');
    }
}
