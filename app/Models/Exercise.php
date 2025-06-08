<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'equipment',
    ];

    public function exerciseSets(): HasMany
    {
        return $this->hasMany(ExerciseSet::class);
    }

    public function workoutSchedules()
    {
        return $this->hasMany(WorkoutSchedule::class);
    }
}
