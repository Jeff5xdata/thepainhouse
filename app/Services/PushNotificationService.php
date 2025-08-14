<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PushNotificationService
{
    /**
     * Send a push notification to the user
     */
    public function sendNotification($userId, $title, $body, $data = [])
    {
        try {
            // Get user's notification preferences
            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                Log::warning("User not found for notification: {$userId}");
                return false;
            }

            // For now, we'll use the browser's built-in notification API
            // In a production app, you might want to integrate with Firebase Cloud Messaging
            // or another push notification service
            
            $notificationData = [
                'title' => $title,
                'body' => $body,
                'icon' => asset('images/hl.png'),
                'badge' => asset('images/hl.png'),
                'data' => $data,
                'requireInteraction' => true,
                'actions' => [
                    [
                        'action' => 'smoke_now',
                        'title' => 'Smoke Now',
                        'icon' => asset('images/hl.png')
                    ],
                    [
                        'action' => 'delay',
                        'title' => 'Delay 15 min',
                        'icon' => asset('images/hl.png')
                    ]
                ]
            ];

            // Dispatch event for the frontend to handle
            event(new \App\Events\PushNotificationSent($userId, $notificationData));
            
            Log::info("Push notification dispatched for user {$userId}: {$title}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to send push notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Schedule a notification for a specific time
     */
    public function scheduleNotification($userId, $title, $body, $scheduledTime, $data = [])
    {
        try {
            $delay = now()->diffInSeconds($scheduledTime);
            
            if ($delay <= 0) {
                // Send immediately if time has passed
                return $this->sendNotification($userId, $title, $body, $data);
            }

            // Schedule the notification
            \Illuminate\Support\Facades\Queue::later(
                now()->addSeconds($delay),
                new \App\Jobs\SendScheduledNotification($userId, $title, $body, $data)
            );

            Log::info("Notification scheduled for user {$userId} at {$scheduledTime}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to schedule notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel scheduled notifications for a user
     */
    public function cancelScheduledNotifications($userId)
    {
        try {
            // This would need to be implemented based on your queue driver
            // For now, we'll just log the request
            Log::info("Cancelled scheduled notifications for user {$userId}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to cancel scheduled notifications: " . $e->getMessage());
            return false;
        }
    }
}
