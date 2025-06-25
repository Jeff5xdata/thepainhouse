<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Mail\NewMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Get messages for the authenticated user
     */
    public function index()
    {
        $user = auth()->user();
        
        $messages = $user->receivedMessages()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /**
     * Get a specific message
     */
    public function show(Message $message)
    {
        $user = auth()->user();

        // Check if user is the recipient
        if ($message->recipient_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access.',
            ], 403);
        }

        // Mark as read if not already
        if (!$message->is_read) {
            $message->markAsRead();
        }

        return response()->json([
            'message' => $message->load('sender'),
        ]);
    }

    /**
     * Send a new message
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        // Check if user is trying to message themselves
        if ($user->id === $request->recipient_id) {
            return response()->json([
                'message' => 'You cannot send a message to yourself.',
            ], 400);
        }

        // Check if recipient exists
        $recipient = User::find($request->recipient_id);
        if (!$recipient) {
            return response()->json([
                'message' => 'Recipient not found.',
            ], 404);
        }

        try {
            $message = Message::create([
                'sender_id' => $user->id,
                'recipient_id' => $request->recipient_id,
                'subject' => $request->subject,
                'content' => $request->content,
            ]);

            // Send email notification
            Mail::to($recipient->email)->queue(new NewMessageNotification($message));

            return response()->json([
                'message' => 'Message sent successfully!',
                'sent_message' => $message->load('recipient'),
            ]);

        } catch (\Exception $e) {
            \Log::error('Message creation error', [
                'sender_id' => $user->id,
                'recipient_id' => $request->recipient_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'An error occurred while sending the message. Please try again.',
            ], 500);
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Message $message)
    {
        $user = auth()->user();

        // Check if user is the recipient
        if ($message->recipient_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $message->markAsRead();

        return response()->json([
            'message' => 'Message marked as read.',
        ]);
    }

    /**
     * Get unread messages count
     */
    public function unreadCount()
    {
        $user = auth()->user();
        
        return response()->json([
            'unread_count' => $user->unreadMessagesCount(),
        ]);
    }

    /**
     * Get conversation with a specific user
     */
    public function conversation($userId)
    {
        $user = auth()->user();
        
        // Check if the other user exists
        $otherUser = User::find($userId);
        if (!$otherUser) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $messages = Message::where(function ($query) use ($user, $userId) {
            $query->where('sender_id', $user->id)
                  ->where('recipient_id', $userId);
        })->orWhere(function ($query) use ($user, $userId) {
            $query->where('sender_id', $userId)
                  ->where('recipient_id', $user->id);
        })
        ->with(['sender', 'recipient'])
        ->orderBy('created_at', 'asc')
        ->get();

        // Mark received messages as read
        $messages->where('recipient_id', $user->id)
                ->where('is_read', false)
                ->each(function ($message) {
                    $message->markAsRead();
                });

        return response()->json([
            'messages' => $messages,
            'other_user' => $otherUser,
        ]);
    }
} 