<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtkCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type'];

    public function items()
    {
        return $this->hasMany(AtkItem::class);
    }
}
