<?php

namespace App\Http\Controllers;

use App\Models\TrainerRequest;
use App\Models\User;
use App\Models\Message;
use App\Mail\TrainerRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class TrainerRequestController extends Controller
{
    /**
     * Store a new trainer request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trainer_email' => 'required|email',
            'message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        // Check if user already has a trainer
        if ($user->hasTrainer()) {
            return response()->json([
                'message' => 'You already have a trainer assigned.',
            ], 400);
        }

        // Check if user is trying to request themselves
        if ($user->email === $request->trainer_email) {
            return response()->json([
                'message' => 'You cannot request yourself as a trainer.',
            ], 400);
        }

        // Check if request already exists
        $existingRequest = TrainerRequest::where('client_id', $user->id)
            ->where('trainer_email', $request->trainer_email)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'message' => 'You have already sent a request to this trainer.',
            ], 400);
        }

        try {
            $trainerRequest = TrainerRequest::create([
                'client_id' => $user->id,
                'trainer_email' => $request->trainer_email,
                'message' => $request->message,
            ]);

            // Send email notification
            Mail::to($request->trainer_email)->queue(new TrainerRequestNotification($trainerRequest));

            return response()->json([
                'message' => 'Trainer request sent successfully!',
                'request' => $trainerRequest,
            ]);

        } catch (\Exception $e) {
            \Log::error('Trainer request creation error', [
                'user_id' => $user->id,
                'trainer_email' => $request->trainer_email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'An error occurred while sending the request. Please try again.',
            ], 500);
        }
    }

    /**
     * Accept a trainer request
     */
    public function accept(TrainerRequest $trainerRequest)
    {
        $user = auth()->user();

        // Check if the user is the intended trainer
        if ($user->email !== $trainerRequest->trainer_email) {
            return response()->json([
                'message' => 'Unauthorized action.',
            ], 403);
        }

        // Check if request is still pending
        if ($trainerRequest->status !== 'pending') {
            return response()->json([
                'message' => 'This request has already been processed.',
            ], 400);
        }

        try {
            // Update request status
            $trainerRequest->update([
                'status' => 'accepted',
                'responded_at' => now(),
            ]);

            // Assign trainer to client
            $client = $trainerRequest->client;
            $client->update(['my_trainer' => $user->id]);

            // Mark user as trainer if not already
            if (!$user->is_trainer) {
                $user->update(['is_trainer' => true]);
            }

            // Add a message to the client's message center about the approval
            \App\Models\Message::create([
                'sender_id' => $user->id,
                'recipient_id' => $client->id,
                'subject' => 'Trainer Request Approved',
                'content' => 'Your trainer request has been approved! You can now start working with your trainer.',
                'is_read' => false,
            ]);

            return response()->json([
                'message' => 'Trainer request accepted successfully!',
                'client' => $client,
            ]);

        } catch (\Exception $e) {
            \Log::error('Trainer request acceptance error', [
                'request_id' => $trainerRequest->id,
                'trainer_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'An error occurred while accepting the request. Please try again.',
            ], 500);
        }
    }

    /**
     * Decline a trainer request
     */
    public function decline(TrainerRequest $trainerRequest)
    {
        $user = auth()->user();

        // Check if the user is the intended trainer
        if ($user->email !== $trainerRequest->trainer_email) {
            return response()->json([
                'message' => 'Unauthorized action.',
            ], 403);
        }

        // Check if request is still pending
        if ($trainerRequest->status !== 'pending') {
            return response()->json([
                'message' => 'This request has already been processed.',
            ], 400);
        }

        try {
            $trainerRequest->update([
                'status' => 'declined',
                'responded_at' => now(),
            ]);

            // Add a message to the client's message center about the rejection
            \App\Models\Message::create([
                'sender_id' => $user->id,
                'recipient_id' => $trainerRequest->client_id,
                'subject' => 'Trainer Request Declined',
                'content' => 'Your trainer request has been declined. You can try requesting another trainer.',
                'is_read' => false,
            ]);

            return response()->json([
                'message' => 'Trainer request declined successfully.',
            ]);

        } catch (\Exception $e) {
            \Log::error('Trainer request decline error', [
                'request_id' => $trainerRequest->id,
                'trainer_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'An error occurred while declining the request. Please try again.',
            ], 500);
        }
    }

    /**
     * Get pending trainer requests for the authenticated user
     */
    public function pending()
    {
        $user = auth()->user();
        
        $pendingRequests = TrainerRequest::where('trainer_email', $user->email)
            ->where('status', 'pending')
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'requests' => $pendingRequests,
        ]);
    }
} 