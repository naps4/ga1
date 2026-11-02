<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $table = 'locations';

    protected $fillable = [
        'plant_code',
        'department_id',
        'room_name',
        'doc_reference',
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class, 'plant_code', 'code');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}

