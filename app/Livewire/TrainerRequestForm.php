<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TrainerRequest;
use App\Mail\TrainerRequestNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class TrainerRequestForm extends Component
{
    public $trainer_email = '';
    public $message = '';
    public $isLoading = false;
    public $successMessage = '';
    public $errorMessage = '';

    protected $rules = [
        'trainer_email' => 'required|email',
        'message' => 'nullable|string|max:1000',
    ];

    public function sendRequest()
    {
        $this->validate();
        $this->isLoading = true;
        $this->successMessage = '';
        $this->errorMessage = '';

        $user = auth()->user();

        // Check if user already has a trainer
        if ($user->hasTrainer()) {
            $this->errorMessage = 'You already have a trainer assigned.';
            $this->isLoading = false;
            return;
        }

        // Check if user is trying to request themselves
        if ($user->email === $this->trainer_email) {
            $this->errorMessage = 'You cannot request yourself as a trainer.';
            $this->isLoading = false;
            return;
        }

        // Check if request already exists
        $existingRequest = TrainerRequest::where('client_id', $user->id)
            ->where('trainer_email', $this->trainer_email)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            $this->errorMessage = 'You have already sent a request to this trainer.';
            $this->isLoading = false;
            return;
        }

        try {
            $trainerRequest = TrainerRequest::create([
                'client_id' => $user->id,
                'trainer_email' => $this->trainer_email,
                'message' => $this->message,
            ]);

            // If the trainer exists, copy the request message to their message center
            $trainer = \App\Models\User::where('email', $this->trainer_email)->first();
            if ($trainer) {
                \App\Models\Message::create([
                    'sender_id' => $user->id,
                    'recipient_id' => $trainer->id,
                    'subject' => 'Trainer Request from ' . $user->name,
                    'content' => 'Trainer Request: ' . ($this->message ?: 'No additional message provided.'),
                    'is_read' => false,
                ]);
            }

            // Add a message to the client's own message center to track the request
            \App\Models\Message::create([
                'sender_id' => $user->id,
                'recipient_id' => $user->id,
                'subject' => 'Trainer Request to ' . $this->trainer_email,
                'content' => 'Trainer Request Status: Pending' . ($this->message ? "\n\nYour message: " . $this->message : ''),
                'is_read' => false,
            ]);

            // Try to send email notification, but don't fail if it doesn't work
            try {
                Mail::to($this->trainer_email)->send(new TrainerRequestNotification($trainerRequest));
            } catch (\Exception $mailException) {
                \Log::warning('Failed to send trainer request email', [
                    'trainer_email' => $this->trainer_email,
                    'error' => $mailException->getMessage(),
                ]);
                // Don't fail the request if email fails
            }

            $this->successMessage = 'Trainer request sent successfully!';
            $this->reset(['trainer_email', 'message']);

        } catch (\Exception $e) {
            \Log::error('Trainer request creation error', [
                'user_id' => $user->id,
                'trainer_email' => $this->trainer_email,
                'error' => $e->getMessage(),
            ]);

            $this->errorMessage = 'An error occurred while sending the request. Please try again.';
        }

        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.trainer-request-form');
    }
} 