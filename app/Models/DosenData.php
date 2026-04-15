<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DosenData extends Model
{
    //
    protected $table='dosens';
    protected $primaryKey='dosen_id';
    protected $keyType='string';
    public $incrementing=false;
    protected $fillable=[
        'dosen_id',
        'nama',
        'user_id',
        'kode',
        'no_wa',
        'foto',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function ujians()
    {
        return $this->hasMany(Ujian::class, 'dosen_id', 'dosen_id');
    }

    public function mataKuliah()
    {
        return $this->hasMany(MataKuliah::class, 'dosen_id', 'dosen_id');
    }

    public function materiUjians()
    {
        return $this->hasMany(MateriUjian::class, 'dosen_id', 'dosen_id');
    }
}
