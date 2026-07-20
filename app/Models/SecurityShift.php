<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
    ];

    public function assignments()
    {
        return $this->hasMany(SecurityAssignment::class, 'shift_id');
    }
}
