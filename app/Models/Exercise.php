<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'equipment',
        'user_id',
    ];

    /**
     * Get the validation rules for creating/updating an exercise
     */
    public static function rules($exerciseId = null)
    {
        $uniqueRule = 'unique:exercises,name';
        if ($exerciseId) {
            $uniqueRule .= ',' . $exerciseId;
        }

        return [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'string', 'max:100'],
            'equipment' => ['required', 'string', 'max:100'],
        ];
    }

    public function exerciseSets(): HasMany
    {
        return $this->hasMany(ExerciseSet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
