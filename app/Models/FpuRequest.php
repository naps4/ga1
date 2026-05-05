<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FpuRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function materials()
    {
        return $this->hasMany(FpuMaterial::class);
    }

    public function verifiedByHrga()
    {
        return $this->belongsTo(User::class, 'verified_by_hrga');
    }

    public function knownByManager()
    {
        return $this->belongsTo(User::class, 'known_by_manager');
    }

    public function approvedByGm()
    {
        return $this->belongsTo(User::class, 'approved_by_gm');
    }
}
