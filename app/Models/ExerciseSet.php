<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'workout_session_id',
        'exercise_id',
        'set_number',
        'weight',
        'reps',
        'is_warmup',
        'completed',
        'notes',
    ];

    protected $casts = [
        'weight' => 'float',
        'reps' => 'integer',
        'is_warmup' => 'boolean',
        'completed' => 'boolean',
    ];

    protected $attributes = [
        'completed' => false,
        'is_warmup' => false,
    ];

    public function workoutSession()
    {
        return $this->belongsTo(WorkoutSession::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    public function getFormattedTimeAttribute(): string
    {
        if (!$this->time_in_seconds) {
            return '';
        }

        if ($this->time_in_seconds < 60) {
            return $this->time_in_seconds . ' sec';
        }

        $minutes = floor($this->time_in_seconds / 60);
        $seconds = $this->time_in_seconds % 60;
        return $minutes . ' min' . ($seconds > 0 ? ' ' . $seconds . ' sec' : '');
    }
}
