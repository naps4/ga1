<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';

    protected $fillable = [
        'code',
        'name',
        'aliases',
    ];

    protected $casts = [
        'aliases' => 'array',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}

