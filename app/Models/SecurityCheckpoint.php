<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityCheckpoint extends Model
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
        return $this->hasMany(SecurityCheckpointLog::class, 'checkpoint_id');
    }
}
