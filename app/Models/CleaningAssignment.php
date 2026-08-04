<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CleaningAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_id',
        'plant',
        'assigned_date',
        'notes',
    ];

    protected $casts = [
        'assigned_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(CleaningShift::class, 'shift_id');
    }
}
