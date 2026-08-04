<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CleaningCheckpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant',
        'sub_area',
        'area_name',
        'code',
        'target_duration',
    ];

    public function logs()
    {
        return $this->hasMany(CleaningCheckpointLog::class, 'checkpoint_id');
    }
}
