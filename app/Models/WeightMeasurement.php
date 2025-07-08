<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeightMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'weight',
        'unit',
        'measurement_date',
        'notes',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'weight' => 'decimal:2',
    ];

    /**
     * Get the user that owns the weight measurement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get measurements for a specific date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('measurement_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get the latest measurement
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('measurement_date', 'desc');
    }

    /**
     * Get weight in kg (convert if needed)
     */
    public function getWeightInKgAttribute()
    {
        if ($this->unit === 'lbs') {
            return $this->weight * 0.453592;
        }
        return $this->weight;
    }

    /**
     * Get weight in lbs (convert if needed)
     */
    public function getWeightInLbsAttribute()
    {
        if ($this->unit === 'kg') {
            return $this->weight * 2.20462;
        }
        return $this->weight;
    }
}
