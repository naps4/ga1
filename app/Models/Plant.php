<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    use HasFactory;

    protected $table = 'plants';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function locations()
    {
        return $this->hasMany(Location::class, 'plant_code', 'code');
    }

    public function currentAssets()
    {
        return $this->hasMany(Asset::class, 'current_plant_code', 'code');
    }

    public function originAssets()
    {
        return $this->hasMany(Asset::class, 'origin_plant_code', 'code');
    }
}

