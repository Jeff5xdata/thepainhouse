<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewMessageNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function build()
    {
        return $this->markdown('emails.new-message')
            ->subject('New Message: ' . $this->message->subject)
            ->with([
                'message' => $this->message,
                'sender' => $this->message->sender,
                'recipient' => $this->message->recipient,
                'loginUrl' => route('login'),
            ]);
    }
} 