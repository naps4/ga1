<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Uom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /**
     * Relasi ke AtkItem berdasarkan nama satuan
     */
    public function atkItems()
    {
        return $this->hasMany(AtkItem::class, 'unit', 'name');
    }
}
