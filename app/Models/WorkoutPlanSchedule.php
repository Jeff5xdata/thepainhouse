<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class WorkoutPlanSchedule extends Model
{
    protected $table = 'workout_plan_schedule';

    /**
     * Cache key for query results
     */
    private static string $cacheKey = 'workout_plan_schedule_cache';

    protected $fillable = [
        'workout_plan_id',
        'exercise_id',
        'week_number',
        'day_of_week',
        'order_in_day',
        'is_time_based',
        'notes',
        'set_details',
    ];

    protected $casts = [
        'is_time_based' => 'boolean',
        'week_number' => 'integer',
        'order_in_day' => 'integer',
        'set_details' => 'array',
    ];

    protected $attributes = [
        'order_in_day' => 0,
        'is_time_based' => false,
    ];

    public function workoutPlan(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlan::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function getDayNameAttribute(): string
    {
        $dayNames = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        ];

        return $dayNames[$this->day_of_week] ?? 'Unknown Day';
    }

    /**
     * Get formatted set details with proper numbering
     */
    public function getFormattedSetDetailsAttribute(): array
    {
        if (empty($this->set_details)) {
            throw new \Exception("set_details is required but empty for schedule item ID: {$this->id}. Please ensure set_details is properly initialized.");
        }

        return $this->set_details;
    }

    /**
     * Generate default set details based on exercise configuration
     * @deprecated This method is deprecated. set_details should always be properly initialized.
     */
    public function generateDefaultSetDetails(): array
    {
        throw new \Exception("generateDefaultSetDetails is deprecated. set_details must be properly initialized with actual set data.");
    }

    /**
     * Update set details and save
     */
    public function updateSetDetails(array $setDetails): void
    {
        if (empty($setDetails)) {
            throw new \Exception("set_details cannot be empty. Please provide valid set data.");
        }
        
        $this->set_details = $setDetails;
        $this->save();
    }

    /**
     * Boot method to ensure set_details is always generated when sets/reps change
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($model) {
            // Ensure set_details is always an array and not empty
            if (empty($model->set_details)) {
                throw new \Exception("set_details is required but empty for schedule item. Please ensure set_details is properly initialized before saving.");
            }
        });
    }

    /**
     * Clear cache for this schedule item
     */
    public static function clearCache(int $workoutPlanId): void
    {
        $cacheKey = "workout_plan_{$workoutPlanId}_schedule";
        Cache::forget($cacheKey);
    }

    /**
     * Get schedule items for a specific workout plan, week, and day
     */
    public static function getScheduleForDay(int $workoutPlanId, int $week, int $day)
    {
        $cacheKey = "workout_plan_{$workoutPlanId}_week_{$week}_day_{$day}";
        
        return Cache::remember($cacheKey, 3600, function () use ($workoutPlanId, $week, $day) {
            return static::where('workout_plan_id', $workoutPlanId)
                ->where('week_number', $week)
                ->where('day_of_week', $day)
                ->with('exercise')
                ->orderBy('order_in_day')
                ->get();
        });
    }
} 