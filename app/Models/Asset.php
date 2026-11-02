<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';

    protected $guarded = ['id'];

    protected $casts = [
        'purchase_date' => 'date',
        'qty' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'useful_life_years' => 'integer',
        'is_price_complete' => 'boolean',
        'disposed_at' => 'datetime',
    ];

    protected $appends = [
        'months_elapsed',
        'monthly_depreciation',
        'yearly_depreciation',
        'accumulated_depreciation',
        'book_value',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function originPlant()
    {
        return $this->belongsTo(Plant::class, 'origin_plant_code', 'code');
    }

    public function currentPlant()
    {
        return $this->belongsTo(Plant::class, 'current_plant_code', 'code');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_code', 'code');
    }

    public function mutations()
    {
        return $this->hasMany(AssetMutation::class)->orderBy('mutation_date', 'desc');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'AKTIF');
    }

    public function scopeDisposed($query)
    {
        return $query->where('status', 'DISPOSED');
    }

    public function scopePlant($query, $plantCode)
    {
        return $query->where('current_plant_code', $plantCode);
    }

    public function scopeGroup($query, $group)
    {
        return $query->where('asset_group', strtoupper($group));
    }

    // ── Real-Time Depreciation Accessors ────────────────────────────────────

    public function getMonthsElapsedAttribute(): int
    {
        if (!$this->purchase_date) {
            return 0;
        }

        $startDate = Carbon::parse($this->purchase_date)->startOfMonth();
        $now = Carbon::now()->startOfMonth();

        if ($startDate->greaterThan($now)) {
            return 0;
        }

        $diffMonths = $startDate->diffInMonths($now);
        $maxMonths = ($this->useful_life_years ?? 4) * 12;

        return min($diffMonths, $maxMonths);
    }

    public function getMonthlyDepreciationAttribute(): float
    {
        $usefulLife = $this->useful_life_years ?: 4;
        $totalMonths = $usefulLife * 12;
        $totalPrice = (float) $this->total_price;

        if ($totalMonths <= 0 || $totalPrice <= 0) {
            return 0.0;
        }

        return round($totalPrice / $totalMonths, 2);
    }

    public function getYearlyDepreciationAttribute(): float
    {
        $usefulLife = $this->useful_life_years ?: 4;
        $totalPrice = (float) $this->total_price;

        if ($usefulLife <= 0 || $totalPrice <= 0) {
            return 0.0;
        }

        return round($totalPrice / $usefulLife, 2);
    }

    public function getAccumulatedDepreciationAttribute(): float
    {
        $accumulated = $this->monthly_depreciation * $this->months_elapsed;
        $totalPrice = (float) $this->total_price;

        return min(round($accumulated, 2), $totalPrice);
    }

    public function getBookValueAttribute(): float
    {
        $totalPrice = (float) $this->total_price;
        $remaining = $totalPrice - $this->accumulated_depreciation;

        return max(0.0, round($remaining, 2));
    }
}

