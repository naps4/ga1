<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityCheckpointLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'checkpoint_id',
        'shift',
        'checked_at',
        'status',
        'notes',
        'selfie_path',
        'attachment_path',
        'is_evident_report',
        'duration_minutes',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'is_evident_report' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function checkpoint()
    {
        return $this->belongsTo(SecurityCheckpoint::class, 'checkpoint_id');
    }
}
