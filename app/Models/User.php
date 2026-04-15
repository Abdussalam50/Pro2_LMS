<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'fcm_token',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function dosen()
    {
        return $this->hasOne(DosenData::class, 'user_id');
    }

    public function dosenData()
    {
        return $this->hasOne(DosenData::class, 'user_id');
    }

    public function mahasiswa()
    {
        return $this->hasOne(MahasiswaData::class, 'user_id');
    }

    public function mahasiswaData()
    {
        return $this->hasOne(MahasiswaData::class, 'user_id');
    }

    public function canAccessPanel(Panel $panel):bool
    {
        return in_array($this->role,['admin','dosen']) && $this->is_active;
    }

    public function kelompokAnggota()
    {
        return $this->hasMany(KelompokAnggota::class, 'user_id');
    }
}
