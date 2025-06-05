<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutSession extends Model
{
    protected $fillable = [
        'workout_plan_id',
        'user_id',
        'date',
        'week_number',
        'day_of_week',
        'status',
        'notes',
        'completed_at',
        'name',
    ];

    protected $casts = [
        'date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function workoutPlan(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exerciseSets(): HasMany
    {
        return $this->hasMany(ExerciseSet::class);
    }

    public function getDayNameAttribute()
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
