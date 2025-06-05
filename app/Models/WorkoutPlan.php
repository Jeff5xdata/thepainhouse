<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutPlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'weeks_duration',
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'weeks_duration' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workoutSessions(): HasMany
    {
        return $this->hasMany(WorkoutSession::class);
    }

    public function scheduleItems(): HasMany
    {
        return $this->hasMany(WorkoutPlanSchedule::class);
    }

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'workout_plan_exercises')
            ->withPivot([
                'default_sets',
                'default_reps',
                'default_weight',
                'notes',
                'has_warmup',
                'warmup_sets',
                'warmup_reps',
                'warmup_weight_percentage'
            ])
            ->withTimestamps();
    }

    /**
     * Get the schedule for a specific week and day
     */
    public function getScheduleForDay(int $week, string $day)
    {
        return $this->scheduleItems()
            ->where('week_number', $week)
            ->where('day_of_week', $day)
            ->orderBy('order_in_day')
            ->with('exercise')
            ->get();
    }
}
