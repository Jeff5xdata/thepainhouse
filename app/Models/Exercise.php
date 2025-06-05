<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
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
}
