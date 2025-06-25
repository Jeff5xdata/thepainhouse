<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainerRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'trainer_email',
        'status',
        'message',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    /**
     * Get the client who made the request
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the trainer (if they exist in the system)
     */
    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_email', 'email');
    }

    /**
     * Scope to get pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get accepted requests
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope to get declined requests
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }
} 