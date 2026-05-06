<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderProgress extends Model
{
    use HasFactory;

    protected $table = 'work_order_progresses';
    protected $guarded = ['id'];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
