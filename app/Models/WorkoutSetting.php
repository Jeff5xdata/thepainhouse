<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutSetting extends Model
{
    protected $fillable = [
        'user_id',
        'default_rest_timer',
        'default_warmup_sets',
        'default_warmup_reps',
        'default_warmup_weight_percentage',
        'default_work_sets',
        'default_work_reps',
    ];

    protected $casts = [
        'default_rest_timer' => 'integer',
        'default_warmup_sets' => 'integer',
        'default_warmup_reps' => 'integer',
        'default_warmup_weight_percentage' => 'integer',
        'default_work_sets' => 'integer',
        'default_work_reps' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 