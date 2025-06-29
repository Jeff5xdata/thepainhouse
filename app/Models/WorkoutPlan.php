<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * Get the cache key for a specific day's schedule
     */
    protected function getScheduleCacheKey(int $week, string $day): string
    {
        return "workout_plan_{$this->id}_week_{$week}_day_{$day}";
    }

    /**
     * Get the schedule for a specific week and day
     */
    public function getScheduleForDay(int $week, string $day)
    {
        $cacheKey = $this->getScheduleCacheKey($week, $day);
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($week, $day) {
            return $this->scheduleItems()
                ->where('week_number', $week)
                ->where('day_of_week', $day)
                ->select(
                    'exercise_id',
                    'workout_plan_id',
                    'week_number',
                    'day_of_week',
                    'has_warmup',
                    'warmup_sets',
                    'warmup_reps',
                    'warmup_weight_percentage',
                    'sets',
                    'reps',
                    'time_in_seconds',
                    'is_time_based',
                    DB::raw('MIN(id) as id'),
                    DB::raw('MIN(order_in_day) as order_in_day'),
                    DB::raw('MIN(weight) as weight'),
                    DB::raw('MIN(notes) as notes'),
                    DB::raw('MIN(warmup_time_in_seconds) as warmup_time_in_seconds'),
                    DB::raw('MIN(created_at) as created_at'),
                    DB::raw('MIN(updated_at) as updated_at')
                )
                ->groupBy(
                    'exercise_id',
                    'workout_plan_id',
                    'week_number',
                    'day_of_week',
                    'has_warmup',
                    'warmup_sets',
                    'warmup_reps',
                    'warmup_weight_percentage',
                    'sets',
                    'reps',
                    'time_in_seconds',
                    'is_time_based'
                )
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
