<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'owner',
        'is_trainer',
        'my_trainer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_trainer' => 'boolean',
        ];
    }

    public function workoutPlans(): HasMany
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    public function workoutSessions(): HasMany
    {
        return $this->hasMany(WorkoutSession::class);
    }

    public function workoutSettings()
    {
        return $this->hasOne(WorkoutSetting::class);
    }

    /**
     * Get the trainer for this user
     */
    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'my_trainer');
    }

    /**
     * Get all clients for this trainer
     */
    public function clients(): HasMany
    {
        return $this->hasMany(User::class, 'my_trainer');
    }

    /**
     * Get all food logs for this user
     */
    public function foodLogs(): HasMany
    {
        return $this->hasMany(FoodLog::class);
    }

    /**
     * Get trainer requests made by this user
     */
    public function trainerRequests(): HasMany
    {
        return $this->hasMany(TrainerRequest::class, 'client_id');
    }

    /**
     * Get trainer requests received by this user (as trainer)
     */
    public function receivedTrainerRequests(): HasMany
    {
        return $this->hasMany(TrainerRequest::class, 'trainer_email', 'email');
    }

    /**
     * Get messages sent by this user
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get messages received by this user
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    /**
     * Get unread messages count
     */
    public function unreadMessagesCount(): int
    {
        return $this->receivedMessages()->unread()->count();
    }

    /**
     * Check if user is a trainer
     */
    public function isTrainer(): bool
    {
        return $this->is_trainer;
    }

    /**
     * Check if user has a trainer
     */
    public function hasTrainer(): bool
    {
        return !is_null($this->my_trainer);
    }
}
