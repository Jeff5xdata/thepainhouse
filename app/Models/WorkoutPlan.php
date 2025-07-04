<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

    public function exercises(): HasManyThrough
    {
        return $this->hasManyThrough(
            Exercise::class,
            WorkoutPlanSchedule::class,
            'workout_plan_id', // Foreign key on workout_plan_schedule table
            'id', // Foreign key on exercises table
            'id', // Local key on workout_plans table
            'exercise_id' // Local key on workout_plan_schedule table
        );
    }

    /**
     * Get the cache key for a specific day's schedule
     */
    protected function getScheduleCacheKey(int $week, int $day): string
    {
        return "workout_plan_{$this->id}_week_{$week}_day_{$day}";
    }

    /**
     * Get the schedule for a specific week and day
     */
    public function getScheduleForDay(int $week, int $day)
    {
        $cacheKey = $this->getScheduleCacheKey($week, $day);
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($week, $day) {
            return $this->scheduleItems()
                ->where('week_number', $week)
                ->where('day_of_week', $day)
                ->orderBy('order_in_day')
                ->with('exercise')
                ->get();
        });
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            Cache::flush();
        });

        static::deleted(function ($model) {
            Cache::flush();
        });
    }
}
