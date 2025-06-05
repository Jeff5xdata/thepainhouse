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
        'reps',
        'weight',
        'completed',
        'notes',
        'is_warmup',
        'time_in_seconds',
        'used_progression',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'is_warmup' => 'boolean',
        'set_number' => 'integer',
        'reps' => 'integer',
        'weight' => 'decimal:2',
        'time_in_seconds' => 'integer',
        'used_progression' => 'boolean',
    ];

    protected $attributes = [
        'completed' => false,
        'is_warmup' => false,
        'used_progression' => false,
    ];

    public function workoutSession(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class);
    }

    public function exercise(): BelongsTo
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
