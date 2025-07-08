<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

class WorkoutSession extends Model
{
    protected $fillable = [
        'user_id',
        'workout_plan_id',
        'name',
        'date',
        'week_number',
        'day_of_week',
        'status',
        'completed_at',
        'notes'
    ];

    protected $casts = [
        'date' => 'datetime',
        'completed_at' => 'datetime',
        'day_of_week' => 'integer',
        'week_number' => 'integer',
    ];

    /**
     * Get validation rules for the model
     */
    public static function rules($id = null)
    {
        return [
            'user_id' => 'required|exists:users,id',
            'workout_plan_id' => 'required|exists:workout_plans,id',
            'name' => 'nullable|string|max:255',
            'date' => 'required|date',
            'week_number' => 'required|integer|min:1',
            'day_of_week' => [
                'required',
                Rule::in([0, 1, 2, 3, 4, 5, 6])
            ],
            'status' => [
                'required',
                Rule::in(['in_progress', 'completed', 'cancelled'])
            ],
            'completed_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workoutPlan()
    {
        return $this->belongsTo(WorkoutPlan::class);
    }

    public function exerciseSets()
    {
        return $this->hasMany(ExerciseSet::class);
    }

    public function getDayNameAttribute()
    {
        $days = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday'
        ];

        return $days[$this->day_of_week] ?? 'Unknown Day';
    }

    public function getTotalVolumeAttribute()
    {
        return $this->exerciseSets()
            ->where('is_warmup', false)
            ->get()
            ->sum(function($set) {
                return $set->weight * $set->reps;
            });
    }
}
