<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Notifikasi extends Model
{
    use HasUuids;

    protected $table = 'notifikasi';
    protected $primaryKey = 'notifikasi_id';
    protected $fillable = ['user_id', 'tipe', 'data', 'dibaca', 'dibaca_at'];
    protected $casts = [
        'data'      => 'array',
        'dibaca'    => 'boolean',
        'dibaca_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function markAsRead(): void
    {
        $this->update(['dibaca' => true, 'dibaca_at' => now()]);
    }

    public function scopeUnread($q) { return $q->where('dibaca', false); }
}
