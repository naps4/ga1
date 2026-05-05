<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WoHandover extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function knownBy()
    {
        return $this->belongsTo(User::class, 'known_by');
    }

    public function acceptedBy()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }
}
