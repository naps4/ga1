<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtkRequestItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function atkRequest()
    {
        return $this->belongsTo(AtkRequest::class);
    }
}
