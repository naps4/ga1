<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtkItem extends Model
{
    use HasFactory;

    protected $fillable = ['atk_category_id', 'name', 'unit', 'options'];

    protected $casts = [
        'options' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(AtkCategory::class, 'atk_category_id');
    }
}
