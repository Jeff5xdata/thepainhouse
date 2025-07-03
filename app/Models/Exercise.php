<?php

namespace App\Models;

// Import necessary traits and relationship classes
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Exercise Model
 * 
 * This model represents exercises in the fitness application.
 * Exercises can be created by users and are used in workout plans and sessions.
 * Each exercise belongs to a user and can have multiple sets performed during workouts.
 */
class Exercise extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * These fields can be filled using mass assignment (create, update, etc.)
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',         // Name of the exercise (e.g., "Bench Press", "Squats")
        'description',  // Detailed description of how to perform the exercise
        'category',     // Exercise category (e.g., "Strength", "Cardio", "Flexibility")
        'equipment',    // Equipment needed for the exercise (e.g., "Barbell", "Bodyweight")
        'user_id',      // Foreign key to the user who created this exercise
    ];

    /**
     * Get the validation rules for creating/updating an exercise
     * 
     * @param int|null $exerciseId - The ID of the exercise being updated (null for new exercises)
     * @return array<string, array> - Array of validation rules for each field
     */
    public static function rules($exerciseId = null)
    {
        // Create unique rule for exercise name
        // For updates, exclude the current exercise from uniqueness check
        $uniqueRule = 'unique:exercises,name';
        if ($exerciseId) {
            $uniqueRule .= ',' . $exerciseId;
        }

        return [
            'name' => [
                'required',      // Name is required
                'string',        // Must be a string
                'max:255',       // Maximum 255 characters
                $uniqueRule      // Must be unique among user's exercises
            ],
            'description' => [
                'nullable',      // Description is optional
                'string',        // Must be a string if provided
                'max:1000'       // Maximum 1000 characters
            ],
            'category' => [
                'required',      // Category is required
                'string',        // Must be a string
                'max:100'        // Maximum 100 characters
            ],
            'equipment' => [
                'required',      // Equipment is required
                'string',        // Must be a string
                'max:100'        // Maximum 100 characters
            ],
        ];
    }

    /**
     * Get all exercise sets for this exercise
     * These are the actual sets performed during workouts
     * @return HasMany
     */
    public function exerciseSets(): HasMany
    {
        return $this->hasMany(ExerciseSet::class);
    }

    /**
     * Get the user who created this exercise
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
