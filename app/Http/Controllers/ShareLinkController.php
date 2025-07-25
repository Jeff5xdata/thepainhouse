<?php

namespace App\Http\Controllers;

use App\Models\ShareLink;
use App\Models\WorkoutPlan;
use App\Mail\WorkoutPlanShared;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ShareLinkController extends Controller
{
    public function generateLink(WorkoutPlan $workoutPlan)
    {
        try {
            \Log::info('Generating share link', [
                'workout_plan_id' => $workoutPlan->id,
                'user_id' => auth()->id()
            ]);

            $shareLink = ShareLink::create([
                'token' => ShareLink::generateToken(),
                'workout_plan_id' => $workoutPlan->id,
                'user_id' => auth()->id(),
                'expires_at' => Carbon::now()->addDays(30),
            ]);

            \Log::info('Share link generated successfully', [
                'share_link_id' => $shareLink->id,
                'token' => $shareLink->token,
                'expires_at' => $shareLink->expires_at->format('Y-m-d H:i:s')
            ]);

            return response()->json([
                'message' => 'Share link generated successfully',
                'share_link' => route('workout-plans.shared', $shareLink->token)
            ]);

        } catch (\Exception $e) {
            \Log::error('Share link generation error', [
                'workout_plan_id' => $workoutPlan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'An error occurred while generating the share link. Please try again later.',
            ], 500);
        }
    }

    public function shareEmails(Request $request, WorkoutPlan $workoutPlan)
    {
        try {
            $request->validate([
                'emails' => 'required|array|max:5',
                'emails.*' => 'required|email|max:255',
                'share_link' => 'required|string|url'
            ], [
                'emails.required' => 'Please provide at least one email address.',
                'emails.array' => 'Invalid email format provided.',
                'emails.max' => 'You can share with up to 5 email addresses at once.',
                'emails.*.required' => 'Email address cannot be empty.',
                'emails.*.email' => 'Please provide valid email addresses.',
                'emails.*.max' => 'Email address is too long (maximum is 255 characters).',
                'share_link.required' => 'Share link is required.',
                'share_link.url' => 'Invalid share link format.'
            ]);

            // Extract and validate token from URL
            $url = $request->share_link;
            $token = null;
            
            // Try to extract token from the URL path
            if (preg_match('/\/shared\/workout-plans\/([a-zA-Z0-9]{32})$/', $url, $matches)) {
                $token = $matches[1];
            } else {
                // Fallback to basename if regex doesn't match
                $token = basename($url);
            }
            
            // Validate token format (should be 32 characters)
            if (!preg_match('/^[a-zA-Z0-9]{32}$/', $token)) {
                throw new \Exception('Invalid share link format.');
            }

            $shareLink = ShareLink::where('token', $token)
                ->where('workout_plan_id', $workoutPlan->id)
                ->where('user_id', auth()->id())
                ->where('expires_at', '>', now())
                ->firstOrFail();

            $failedEmails = [];
            foreach ($request->emails as $email) {
                try {
                    Mail::to($email)->queue(new WorkoutPlanShared($shareLink));
                } catch (\Exception $e) {
                    $failedEmails[] = $email;
                    \Log::error("Failed to send email to {$email}", [
                        'share_link_id' => $shareLink->id,
                        'email' => $email,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            if (!empty($failedEmails)) {
                throw new \Exception("Failed to send emails to: " . implode(', ', $failedEmails));
            }

            return response()->json([
                'message' => 'Emails sent successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Validation failed during share process', [
                'workout_plan_id' => $workoutPlan->id,
                'errors' => $e->errors()
            ]);

            return response()->json([
                'message' => $e->errors()['emails'][0] ?? 'Invalid email addresses provided.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Share email error', [
                'workout_plan_id' => $workoutPlan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => $e->getMessage() ?: 'An error occurred while sharing the workout plan. Please try again later.',
            ], 500);
        }
    }

    public function show($token)
    {
        try {
            $shareLink = ShareLink::where('token', $token)
                ->where('expires_at', '>', now())
                ->firstOrFail();

            $workoutPlan = $shareLink->workoutPlan;

            if (!$workoutPlan) {
                throw new \Exception('Workout plan not found');
            }

            return view('workout-plans.shared', [
                'workoutPlan' => $workoutPlan,
                'shareLink' => $shareLink,
                'isGuest' => !auth()->check()
            ]);
        } catch (\Exception $e) {
            return redirect()->route('welcome')->with('error', 'This shared link is invalid or has expired.');
        }
    }
} 