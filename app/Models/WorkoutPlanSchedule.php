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
        'sets',
        'reps',
        'weight',
        'time_in_seconds',
        'notes',
        'has_warmup',
        'warmup_sets',
        'warmup_reps',
        'warmup_time_in_seconds',
        'warmup_weight_percentage',
        'set_details',
    ];

    protected $casts = [
        'is_time_based' => 'boolean',
        'has_warmup' => 'boolean',
        'week_number' => 'integer',
        'order_in_day' => 'integer',
        'sets' => 'integer',
        'reps' => 'integer',
        'weight' => 'decimal:2',
        'time_in_seconds' => 'integer',
        'warmup_sets' => 'integer',
        'warmup_reps' => 'integer',
        'warmup_time_in_seconds' => 'integer',
        'warmup_weight_percentage' => 'integer',
        'set_details' => 'array',
    ];

    protected $attributes = [
        'order_in_day' => 0,
        'is_time_based' => false,
        'has_warmup' => false,
        'sets' => 3,
        'reps' => 10,
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
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday'
        ];

        return $dayNames[$this->day_of_week] ?? ucfirst($this->day_of_week);
    }

    /**
     * Get formatted set details with proper numbering
     */
    public function getFormattedSetDetailsAttribute(): array
    {
        if (empty($this->set_details)) {
            return $this->generateDefaultSetDetails();
        }

        return $this->set_details;
    }

    /**
     * Generate default set details based on exercise configuration
     */
    public function generateDefaultSetDetails(): array
    {
        $setDetails = [];
        $setNumber = 1;

        // Add warmup sets if enabled
        if ($this->has_warmup && $this->warmup_sets > 0) {
            for ($i = 1; $i <= $this->warmup_sets; $i++) {
                $setDetails[] = [
                    'set_number' => $setNumber++,
                    'reps' => $this->warmup_reps,
                    'weight' => null, // Will be calculated based on percentage
                    'notes' => "Warmup Set {$i}",
                    'time_in_seconds' => $this->warmup_time_in_seconds,
                    'is_warmup' => true,
                ];
            }
        }

        // Add work sets
        for ($i = 1; $i <= $this->sets; $i++) {
            $setDetails[] = [
                'set_number' => $setNumber++,
                'reps' => $this->is_time_based ? 0 : $this->reps,
                'weight' => $this->weight,
                'notes' => "Work Set {$i}",
                'time_in_seconds' => $this->time_in_seconds,
                'is_warmup' => false,
            ];
        }

        return $setDetails;
    }

    /**
     * Update set details and save
     */
    public function updateSetDetails(array $setDetails): void
    {
        $this->set_details = $setDetails;
        $this->save();
    }

    /**
     * Update sets and reps, then regenerate set_details to keep them in sync
     */
    public function updateSetsAndReps(int $sets, int $reps, ?int $warmupSets = null, ?int $warmupReps = null): void
    {
        $this->sets = $sets;
        $this->reps = $reps;
        
        if ($warmupSets !== null) {
            $this->warmup_sets = $warmupSets;
        }
        if ($warmupReps !== null) {
            $this->warmup_reps = $warmupReps;
        }
        
        // Regenerate set_details to keep JSON in sync with columns
        $this->set_details = $this->generateDefaultSetDetails();
        $this->save();
    }

    /**
     * Boot method to ensure set_details is always generated when sets/reps change
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($model) {
            // If set_details is empty but sets/reps are set, generate default set_details
            if (empty($model->set_details) && $model->sets > 0) {
                $model->set_details = $model->generateDefaultSetDetails();
            }
        });
    }

    /**
     * Get the total number of sets (warmup + work)
     */
    public function getTotalSetsAttribute(): int
    {
        $total = $this->sets;
        if ($this->has_warmup && $this->warmup_sets > 0) {
            $total += $this->warmup_sets;
        }
        return $total;
    }

    /**
     * Get the total number of reps (warmup + work)
     */
    public function getTotalRepsAttribute(): int
    {
        $total = $this->sets * $this->reps;
        if ($this->has_warmup && $this->warmup_sets > 0) {
            $total += $this->warmup_sets * $this->warmup_reps;
        }
        return $total;
    }

    /**
     * Get the total time in seconds
     */
    public function getTotalTimeAttribute(): int
    {
        $total = $this->sets * ($this->time_in_seconds ?? 0);
        if ($this->has_warmup && $this->warmup_sets > 0) {
            $total += $this->warmup_sets * ($this->warmup_time_in_seconds ?? 0);
        }
        return $total;
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
    public static function getScheduleForDay(int $workoutPlanId, int $week, string $day)
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