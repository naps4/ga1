<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedEngineer()
    {
        return $this->belongsTo(User::class, 'assigned_engineer_id');
    }

    public function fpuRequest()
    {
        return $this->hasOne(FpuRequest::class);
    }

    public function handover()
    {
        return $this->hasOne(WoHandover::class);
    }

    public function progresses()
    {
        return $this->hasMany(WorkOrderProgress::class);
    }
}
