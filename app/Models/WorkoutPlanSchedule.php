<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutPlanSchedule extends Model
{
    protected $table = 'workout_plan_schedule';

    protected $fillable = [
        'workout_plan_id',
        'exercise_id',
        'week_number',
        'day_of_week',
        'order_in_day',
        'is_time_based',
        'sets',
        'reps',
        'time_in_seconds',
        'has_warmup',
        'warmup_sets',
        'warmup_reps',
        'warmup_time_in_seconds',
        'warmup_weight_percentage',
    ];

    protected $casts = [
        'is_time_based' => 'boolean',
        'has_warmup' => 'boolean',
        'week_number' => 'integer',
        'order_in_day' => 'integer',
        'sets' => 'integer',
        'reps' => 'integer',
        'time_in_seconds' => 'integer',
        'warmup_sets' => 'integer',
        'warmup_reps' => 'integer',
        'warmup_time_in_seconds' => 'integer',
        'warmup_weight_percentage' => 'integer',
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
        $days = [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday'
        ];

        return $days[$this->day_of_week] ?? ucfirst($this->day_of_week);
    }
} 