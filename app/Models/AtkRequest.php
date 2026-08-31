<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtkRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'approved_at' => 'datetime',
        'diberikan_pada' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function managerApprover()
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }

    public function items()
    {
        return $this->hasMany(AtkRequestItem::class);
    }

    public function parent()
    {
        return $this->belongsTo(AtkRequest::class, 'parent_id');
    }

    public function revisions()
    {
        return $this->hasMany(AtkRequest::class, 'parent_id')->orderBy('revision_number', 'asc');
    }

    public function latestRevision()
    {
        return $this->hasOne(AtkRequest::class, 'parent_id')->latestOfMany('revision_number');
    }

    public function getRootParent()
    {
        if (!$this->parent_id) {
            return $this;
        }

        $current = $this;
        while ($current->parent_id && $current->parent) {
            $current = $current->parent;
        }
        return $current;
    }

    public function getFormattedDocNumberAttribute(): string
    {
        $prefix = ($this->request_type === 'Rumah Tangga') ? 'RT' : 'ATK';
        $rootId = $this->parent_id ? $this->getRootParent()->id : $this->id;
        $baseNumber = $prefix . '-' . str_pad($rootId, 4, '0', STR_PAD_LEFT);

        if ($this->revision_number > 0) {
            return $baseNumber . '-R' . $this->revision_number;
        }

        return $baseNumber;
    }

    public function isRevision(): bool
    {
        return $this->revision_number > 0 || !empty($this->parent_id);
    }
}

