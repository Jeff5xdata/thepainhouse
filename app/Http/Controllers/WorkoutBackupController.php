<?php

namespace App\Http\Controllers;

use App\Models\WorkoutPlan;
use App\Models\WorkoutSession;
use App\Models\ExerciseSet;
use App\Models\WorkoutSetting;
use App\Models\Exercise;
use App\Models\FoodLog;
use App\Models\FoodItem;
use App\Models\WeightMeasurement;
use App\Models\BodyMeasurement;
use App\Models\User;
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
                'version' => '1.1', // Updated version to include new data types
                'created_at' => now()->toISOString(),
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_trainer' => $user->is_trainer,
                    'has_trainer' => $user->hasTrainer(),
                ],
                'workout_plans' => [],
                'workout_sessions' => [],
                'exercise_sets' => [],
                'workout_settings' => null,
                'food_tracker' => [
                    'food_logs' => [],
                    'food_items' => [],
                ],
                'body_tracking' => [
                    'weight_measurements' => [],
                    'body_measurements' => [],
                ],
                'trainer_data' => null,
                'client_data' => [],
            ];

            // Get workout plans with exercises and schedules
            $workoutPlans = WorkoutPlan::with([
                'exercises',
                'scheduleItems'
            ])->where('user_id', $user->id)->get();

            foreach ($workoutPlans as $plan) {
                $planData = $plan->toArray();
                $planData['exercises'] = $plan->exercises->toArray();
                $planData['schedule_items'] = $plan->scheduleItems->toArray();
                $backupData['workout_plans'][] = $planData;
            }

            // Get workout sessions
            $workoutSessions = WorkoutSession::where('user_id', $user->id)->get();
            foreach ($workoutSessions as $session) {
                $backupData['workout_sessions'][] = $session->toArray();
            }

            // Get exercise sets
            $exerciseSets = ExerciseSet::where('user_id', $user->id)->get();
            foreach ($exerciseSets as $set) {
                $backupData['exercise_sets'][] = $set->toArray();
            }

            // Get workout settings
            $settings = $user->workoutSettings;
            if ($settings) {
                $backupData['workout_settings'] = $settings->toArray();
            }

            // Get food tracker data
            $foodLogs = FoodLog::where('user_id', $user->id)->with('foodItem')->get();
            foreach ($foodLogs as $log) {
                $logData = $log->toArray();
                $logData['food_item'] = $log->foodItem ? $log->foodItem->toArray() : null;
                $backupData['food_tracker']['food_logs'][] = $logData;
            }

            // Get unique food items used by this user
            $foodItemIds = $foodLogs->pluck('food_item_id')->unique();
            $foodItems = FoodItem::whereIn('id', $foodItemIds)->get();
            foreach ($foodItems as $item) {
                $backupData['food_tracker']['food_items'][] = $item->toArray();
            }

            // Get body tracking data
            $weightMeasurements = WeightMeasurement::where('user_id', $user->id)->get();
            foreach ($weightMeasurements as $measurement) {
                $backupData['body_tracking']['weight_measurements'][] = $measurement->toArray();
            }

            $bodyMeasurements = BodyMeasurement::where('user_id', $user->id)->get();
            foreach ($bodyMeasurements as $measurement) {
                $backupData['body_tracking']['body_measurements'][] = $measurement->toArray();
            }

            // If user is a trainer, include client data
            if ($user->is_trainer) {
                $clients = $user->clients()->with([
                    'workoutPlans',
                    'workoutSessions',
                    'exerciseSets',
                    'workoutSettings',
                    'foodLogs.foodItem',
                    'weightMeasurements',
                    'bodyMeasurements'
                ])->get();

                foreach ($clients as $client) {
                    $clientData = [
                        'client_info' => [
                            'id' => $client->id,
                            'name' => $client->name,
                            'email' => $client->email,
                        ],
                        'workout_plans' => [],
                        'workout_sessions' => [],
                        'exercise_sets' => [],
                        'workout_settings' => null,
                        'food_tracker' => [
                            'food_logs' => [],
                            'food_items' => [],
                        ],
                        'body_tracking' => [
                            'weight_measurements' => [],
                            'body_measurements' => [],
                        ],
                    ];

                    // Client workout plans
                    foreach ($client->workoutPlans as $plan) {
                        $planData = $plan->toArray();
                        $planData['exercises'] = $plan->exercises->toArray();
                        $planData['schedule_items'] = $plan->scheduleItems->toArray();
                        $clientData['workout_plans'][] = $planData;
                    }

                    // Client workout sessions
                    foreach ($client->workoutSessions as $session) {
                        $clientData['workout_sessions'][] = $session->toArray();
                    }

                    // Client exercise sets
                    foreach ($client->exerciseSets as $set) {
                        $clientData['exercise_sets'][] = $set->toArray();
                    }

                    // Client workout settings
                    if ($client->workoutSettings) {
                        $clientData['workout_settings'] = $client->workoutSettings->toArray();
                    }

                    // Client food tracker data
                    foreach ($client->foodLogs as $log) {
                        $logData = $log->toArray();
                        $logData['food_item'] = $log->foodItem ? $log->foodItem->toArray() : null;
                        $clientData['food_tracker']['food_logs'][] = $logData;
                    }

                    // Client food items
                    $clientFoodItemIds = $client->foodLogs->pluck('food_item_id')->unique();
                    $clientFoodItems = FoodItem::whereIn('id', $clientFoodItemIds)->get();
                    foreach ($clientFoodItems as $item) {
                        $clientData['food_tracker']['food_items'][] = $item->toArray();
                    }

                    // Client body tracking data
                    foreach ($client->weightMeasurements as $measurement) {
                        $clientData['body_tracking']['weight_measurements'][] = $measurement->toArray();
                    }

                    foreach ($client->bodyMeasurements as $measurement) {
                        $clientData['body_tracking']['body_measurements'][] = $measurement->toArray();
                    }

                    $backupData['client_data'][] = $clientData;
                }
            }

            // If user has a trainer, include trainer data
            if ($user->hasTrainer()) {
                $trainer = $user->trainer;
                $backupData['trainer_data'] = [
                    'trainer_info' => [
                        'id' => $trainer->id,
                        'name' => $trainer->name,
                        'email' => $trainer->email,
                    ],
                ];
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