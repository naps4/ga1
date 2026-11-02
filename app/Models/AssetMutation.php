<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMutation extends Model
{
    use HasFactory;

    protected $table = 'asset_mutations';

    protected $guarded = ['id'];

    protected $casts = [
        'mutation_date' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function fromPlant()
    {
        return $this->belongsTo(Plant::class, 'from_plant_code', 'code');
    }

    public function toPlant()
    {
        return $this->belongsTo(Plant::class, 'to_plant_code', 'code');
    }

    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function fromDepartment()
    {
        return $this->belongsTo(Department::class, 'from_dept_id');
    }

    public function toDepartment()
    {
        return $this->belongsTo(Department::class, 'to_dept_id');
    }
}

