<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BodyMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'measurement_date',
        'chest',
        'waist',
        'hips',
        'biceps',
        'forearms',
        'thighs',
        'calves',
        'neck',
        'shoulders',
        'body_fat_percentage',
        'muscle_mass',
        'height',
        'notes',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'chest' => 'decimal:1',
        'waist' => 'decimal:1',
        'hips' => 'decimal:1',
        'biceps' => 'decimal:1',
        'forearms' => 'decimal:1',
        'thighs' => 'decimal:1',
        'calves' => 'decimal:1',
        'neck' => 'decimal:1',
        'shoulders' => 'decimal:1',
        'body_fat_percentage' => 'decimal:1',
        'muscle_mass' => 'decimal:1',
        'height' => 'decimal:1',
    ];

    /**
     * Get the user that owns the body measurement.
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
     * Get BMI calculation
     */
    public function getBmiAttribute()
    {
        if (!$this->height) {
            return null;
        }
        
        // Get the latest weight measurement for this user
        $latestWeight = $this->user->weightMeasurements()
            ->where('date', '<=', $this->measurement_date)
            ->latest('date')
            ->first();
            
        if (!$latestWeight) {
            return null;
        }
        
        $heightInMeters = $this->height / 100;
        return $latestWeight->weight_in_kg / ($heightInMeters * $heightInMeters);
    }

    /**
     * Get all measurements as an array for easy access
     */
    public function getAllMeasurementsAttribute()
    {
        return [
            'chest' => $this->chest,
            'waist' => $this->waist,
            'hips' => $this->hips,
            'biceps' => $this->biceps,
            'forearms' => $this->forearms,
            'thighs' => $this->thighs,
            'calves' => $this->calves,
            'neck' => $this->neck,
            'shoulders' => $this->shoulders,
            'body_fat_percentage' => $this->body_fat_percentage,
            'muscle_mass' => $this->muscle_mass,
            'height' => $this->height,
        ];
    }
}
