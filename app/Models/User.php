<?php

namespace App\Models;

// Import necessary traits and classes for user authentication and relationships
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User Model
 * 
 * This model represents users in the fitness application.
 * It handles authentication, trainer-client relationships, and user data management.
 * Users can be regular clients, trainers, or both.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * These fields can be filled using mass assignment (create, update, etc.)
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',         // User's full name
        'email',        // User's email address (used for login)
        'password',     // User's hashed password
        'owner',        // Boolean flag indicating if user is an application owner
        'is_trainer',   // Boolean flag indicating if user is a trainer
        'my_trainer',   // Foreign key to the user's trainer (if they have one)
        'weight_unit_preference', // User's preferred weight unit (kg or lbs)
    ];

    /**
     * The attributes that should be hidden for serialization.
     * These fields will not be included when the model is converted to JSON/array
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',         // Hide password for security
        'remember_token',   // Hide remember token for security
    ];

    /**
     * Get the attributes that should be cast.
     * Defines how certain attributes should be converted when accessed
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',  // Cast to Carbon datetime instance
            'password' => 'hashed',             // Automatically hash password
            'owner' => 'boolean',               // Cast to boolean
            'is_trainer' => 'boolean',          // Cast to boolean
        ];
    }

    /**
     * Get all workout plans created by this user
     * @return HasMany
     */
    public function workoutPlans(): HasMany
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    /**
     * Get all workout sessions for this user
     * @return HasMany
     */
    public function workoutSessions(): HasMany
    {
        return $this->hasMany(WorkoutSession::class);
    }

    /**
     * Get the workout settings for this user
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function workoutSettings()
    {
        return $this->hasOne(WorkoutSetting::class);
    }

    /**
     * Get the trainer for this user (if they have one)
     * @return BelongsTo
     */
    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'my_trainer');
    }

    /**
     * Get all clients for this trainer (if user is a trainer)
     * @return HasMany
     */
    public function clients(): HasMany
    {
        return $this->hasMany(User::class, 'my_trainer');
    }

    /**
     * Get all food logs for this user
     * @return HasMany
     */
    public function foodLogs(): HasMany
    {
        return $this->hasMany(FoodLog::class);
    }

    /**
     * Get all weight measurements for this user
     * @return HasMany
     */
    public function weightMeasurements(): HasMany
    {
        return $this->hasMany(WeightMeasurement::class);
    }

    /**
     * Get all body measurements for this user
     * @return HasMany
     */
    public function bodyMeasurements(): HasMany
    {
        return $this->hasMany(BodyMeasurement::class);
    }

    /**
     * Get trainer requests made by this user (as a client)
     * @return HasMany
     */
    public function trainerRequests(): HasMany
    {
        return $this->hasMany(TrainerRequest::class, 'client_id');
    }

    /**
     * Get trainer requests received by this user (as a trainer)
     * Matches by trainer's email address
     * @return HasMany
     */
    public function receivedTrainerRequests(): HasMany
    {
        return $this->hasMany(TrainerRequest::class, 'trainer_email', 'email');
    }

    /**
     * Get messages sent by this user
     * @return HasMany
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get messages received by this user
     * @return HasMany
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    /**
     * Get the count of unread messages for this user
     * @return int
     */
    public function unreadMessagesCount(): int
    {
        return $this->receivedMessages()->unread()->count();
    }

    /**
     * Check if user has any unread messages
     * @return bool
     */
    public function hasUnreadMessages(): bool
    {
        return $this->unreadMessagesCount() > 0;
    }

    /**
     * Get unread messages for this user
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function unreadMessages()
    {
        return $this->receivedMessages()->unread();
    }

    /**
     * Mark all messages as read for this user
     * Updates the is_read flag and sets read_at timestamp
     * @return void
     */
    public function markAllMessagesAsRead(): void
    {
        $this->receivedMessages()->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Check if user is a trainer
     * @return bool
     */
    public function isTrainer(): bool
    {
        return $this->is_trainer;
    }

    /**
     * Check if user has a trainer assigned
     * @return bool
     */
    public function hasTrainer(): bool
    {
        return !is_null($this->my_trainer);
    }

    /**
     * Get the user's preferred weight unit
     * @return string
     */
    public function getPreferredWeightUnit(): string
    {
        return $this->weight_unit_preference ?? 'kg';
    }
}
