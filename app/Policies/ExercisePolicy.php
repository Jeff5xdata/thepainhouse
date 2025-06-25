<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExercisePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view exercises
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Exercise $exercise): bool
    {
        return true; // All authenticated users can view exercises
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create a custom exercise
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Exercise $exercise): bool
    {
        // Global exercise: only owner can update
        if (is_null($exercise->user_id)) {
            return $user->owner === 'true' || $user->owner === true || $user->owner === 1;
        }
        // Custom exercise: only creator can update
        return $exercise->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Exercise $exercise): bool
    {
        // Global exercise: only owner can delete
        if (is_null($exercise->user_id)) {
            return $user->owner === 'true' || $user->owner === true || $user->owner === 1;
        }
        // Custom exercise: only creator can delete
        return $exercise->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Exercise $exercise): bool
    {
        return $user->id === 1 || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Exercise $exercise): bool
    {
        return $user->id === 1 || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can manage exercises (general management permission).
     */
    public function manage(User $user): bool
    {
        return $user->id === 1 || $user->hasRole('admin');
    }
}
