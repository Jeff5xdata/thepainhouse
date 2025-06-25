<?php

namespace App\Http\Controllers;

use App\Models\WorkoutPlan;
use App\Models\WorkoutSession;
use App\Models\ExerciseSet;
use App\Models\WorkoutSetting;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class WorkoutBackupController extends Controller
{
    public function downloadBackup()
    {
        try {
            $user = auth()->user();
            
            // Collect all user data
            $backupData = [
                'version' => '1.0',
                'created_at' => now()->toISOString(),
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'workout_plans' => [],
                'workout_sessions' => [],
                'exercise_sets' => [],
                'workout_settings' => null,
            ];

            // Get workout plans with exercises and schedules
            $workoutPlans = WorkoutPlan::with([
                'exercises',
                'scheduleItems'
            ])->where('user_id', $user->id)->get();

            foreach ($workoutPlans as $plan) {
                $planData = $plan->toArray();
                $planData['exercises'] = $plan->exercises->map(function ($exercise) {
                    return [
                        'exercise_id' => $exercise->id,
                        'exercise_name' => $exercise->name,
                        'pivot' => $exercise->pivot->toArray(),
                    ];
                })->toArray();
                $planData['schedule_items'] = $plan->scheduleItems->map(function ($item) {
                    $exercise = Exercise::find($item->exercise_id);
                    return [
                        'exercise_id' => $item->exercise_id,
                        'exercise_name' => $exercise ? $exercise->name : 'Unknown Exercise',
                        'schedule_data' => $item->toArray(),
                    ];
                })->toArray();
                
                $backupData['workout_plans'][] = $planData;
            }

            // Get workout sessions
            $workoutSessions = WorkoutSession::where('user_id', $user->id)->get();
            $backupData['workout_sessions'] = $workoutSessions->toArray();

            // Get exercise sets
            $exerciseSets = ExerciseSet::whereHas('workoutSession', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with(['exercise', 'workoutSession'])->get();
            
            $backupData['exercise_sets'] = $exerciseSets->map(function ($set) {
                return [
                    'set_data' => $set->toArray(),
                    'exercise_name' => $set->exercise ? $set->exercise->name : 'Unknown Exercise',
                    'session_date' => $set->workoutSession ? $set->workoutSession->date : null,
                ];
            })->toArray();

            // Get workout settings
            $settings = $user->workoutSettings;
            if ($settings) {
                $backupData['workout_settings'] = $settings->toArray();
            }

            // Create JSON file
            $filename = 'workout_backup_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.json';
            $jsonData = json_encode($backupData, JSON_PRETTY_PRINT);
            
            // Store temporarily and return download
            Storage::disk('local')->put('backups/' . $filename, $jsonData);
            
            return response()->download(
                Storage::disk('local')->path('backups/' . $filename),
                $filename,
                ['Content-Type' => 'application/json']
            )->deleteFileAfterSend();

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create backup: ' . $e->getMessage());
        }
    }
} 