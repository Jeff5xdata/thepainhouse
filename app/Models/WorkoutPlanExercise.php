<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutPlanExercise extends Model
{
    protected $fillable = [
        'workout_plan_id',
        'exercise_id',
        'default_sets',
        'default_reps',
        'default_weight',
        'notes',
        'has_warmup',
        'warmup_sets',
        'warmup_reps',
        'warmup_weight_percentage',
    ];

    public function workoutPlan()
    {
        return $this->belongsTo(WorkoutPlan::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
