<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kategori',
        'subjek',
        'pesan',
        'status',
        'prioritas',
        'balasan_admin',
        'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
