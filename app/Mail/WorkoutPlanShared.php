<?php

namespace App\Mail;

use App\Models\ShareLink;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WorkoutPlanShared extends Mailable
{
    use Queueable, SerializesModels;

    public $shareLink;

    public function __construct(ShareLink $shareLink)
    {
        $this->shareLink = $shareLink;
    }

    public function build()
    {
        try {
            \Log::info('Building workout plan share email', [
                'share_link_id' => $this->shareLink->id,
                'workout_plan_id' => $this->shareLink->workout_plan_id,
                'workout_plan_name' => $this->shareLink->workoutPlan->name,
                'sender_id' => $this->shareLink->user_id,
                'expires_at' => $this->shareLink->expires_at->format('Y-m-d H:i:s'),
                'smtp_host' => config('mail.mailers.smtp.host'),
                'smtp_port' => config('mail.mailers.smtp.port'),
                'smtp_encryption' => config('mail.mailers.smtp.encryption'),
                'smtp_username' => config('mail.mailers.smtp.username')
            ]);

            return $this->markdown('emails.workout-plan-shared')
                ->subject('A Workout Plan has been shared with you!')
                ->withSwiftMessage(function ($message) {
                    $message->getHeaders()->addTextHeader('X-Transport', 'SMTP');
                    // Enable SMTP debugging
                    $transport = $message->getHeaders()->get('X-Transport');
                    if ($transport) {
                        \Log::info('SMTP Debug: Transport initialized', [
                            'transport' => $transport->getFieldBody(),
                            'message_id' => $message->getId()
                        ]);
                    }
                })
                ->with([
                    'workoutPlan' => $this->shareLink->workoutPlan,
                    'shareLink' => $this->shareLink,
                    'expiresAt' => $this->shareLink->expires_at->format('F j, Y'),
                    'registerUrl' => route('register')
                ]);
        } catch (\Exception $e) {
            \Log::error('Failed to build workout plan share email', [
                'share_link_id' => $this->shareLink->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'smtp_settings' => [
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'encryption' => config('mail.mailers.smtp.encryption'),
                    'username' => config('mail.mailers.smtp.username')
                ]
            ]);
            throw $e;
        }
    }
} 