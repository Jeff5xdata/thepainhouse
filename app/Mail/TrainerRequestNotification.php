<?php

namespace App\Mail;

use App\Models\TrainerRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrainerRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $trainerRequest;

    public function __construct(TrainerRequest $trainerRequest)
    {
        $this->trainerRequest = $trainerRequest;
    }

    public function build()
    {
        return $this->markdown('emails.trainer-request')
            ->subject('Trainer Request from ' . $this->trainerRequest->client->name)
            ->with([
                'client' => $this->trainerRequest->client,
                'trainerRequest' => $this->trainerRequest,
                'loginUrl' => route('login'),
                'registerUrl' => route('register'),
            ]);
    }
} 