<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtkRequestItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_custom' => 'boolean',
        'is_revised' => 'boolean',
        'quantity' => 'integer',
        'previous_quantity' => 'integer',
    ];

    public function atkRequest()
    {
        return $this->belongsTo(AtkRequest::class);
    }

    public function originalItem()
    {
        return $this->belongsTo(AtkRequestItem::class, 'original_item_id');
    }
}

