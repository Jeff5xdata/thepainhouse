<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GeminiApiService - AI-powered workout generation service
 * 
 * Uses Google's Gemini API to generate personalized workout plans based on:
 * - Week duration
 * - Split preferences (2 days on/1 day off, 3 days on/1 day off, etc.)
 * - User preferences and goals
 */
class GeminiApiService
{
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    private ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Generate a workout plan using Gemini AI
     * 
     * @param array $parameters
     * @return array|null
     */
    public function generateWorkoutPlan(array $parameters): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('Gemini API key not configured');
            return null;
        }

        try {
            $prompt = $this->buildWorkoutPrompt($parameters);
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 8192,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $generatedText = $data['candidates'][0]['content']['parts'][0]['text'];
                    return $this->parseWorkoutResponse($generatedText, $parameters);
                }
            }

            Log::warning('Gemini API request failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Gemini API error', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Build the prompt for workout generation
     * 
     * @param array $parameters
     * @return string
     */
    private function buildWorkoutPrompt(array $parameters): string
    {
        $weeksDuration = $parameters['weeks_duration'] ?? 4;
        $splitType = $parameters['split_type'] ?? '2_on_1_off';
        $fitnessLevel = $parameters['fitness_level'] ?? 'intermediate';
        $goals = $parameters['goals'] ?? 'general_fitness';
        $equipment = $parameters['equipment'] ?? 'gym';
        $timePerWorkout = $parameters['time_per_workout'] ?? 60;

        $splitDescription = $this->getSplitDescription($splitType);
        $goalsDescription = $this->getGoalsDescription($goals);
        $equipmentDescription = $this->getEquipmentDescription($equipment);

        return "You are a professional fitness trainer and workout planner. Create a detailed {$weeksDuration}-week workout plan with the following specifications:

**Plan Requirements:**
- Duration: {$weeksDuration} weeks
- Split: {$splitDescription}
- Fitness Level: {$fitnessLevel}
- Goals: {$goalsDescription}
- Equipment: {$equipmentDescription}
- Time per workout: {$timePerWorkout} minutes

**Response Format:**
Please respond with a JSON object in the following exact format:

{
  \"plan_name\": \"[Creative plan name]\",
  \"description\": \"[Brief description of the plan]\",
  \"weeks\": [
    {
      \"week_number\": 1,
      \"days\": [
        {
          \"day_number\": 1,
          \"day_name\": \"Monday\",
          \"workout_type\": \"[e.g., Push, Pull, Legs, Upper, Lower, Full Body]\",
          \"exercises\": [
            {
              \"name\": \"[Exercise name]\",
              \"category\": \"[chest, back, legs, shoulders, arms, core, cardio]\",
              \"equipment\": \"[barbell, dumbbell, bodyweight, machine, cable]\",
              \"sets\": 3,
              \"reps\": \"8-12\",
              \"rest_time\": \"90 seconds\",
              \"notes\": \"[Optional form tips or variations]\"
            }
          ]
        }
      ]
    }
  ]
}

**Guidelines:**
- Include 4-8 exercises per workout
- Vary exercise selection across weeks for progression
- Include proper warm-up and cool-down recommendations
- Ensure exercises match the equipment available
- Consider the split pattern when designing workouts
- Include compound movements as primary exercises
- Add isolation exercises as needed
- Provide realistic rest periods between sets
- Consider the fitness level when choosing exercise difficulty

**Important:** Respond ONLY with the JSON object. Do not include any additional text, explanations, or markdown formatting.";
    }

    /**
     * Parse the AI response into a structured workout plan
     * 
     * @param string $response
     * @param array $parameters
     * @return array|null
     */
    private function parseWorkoutResponse(string $response, array $parameters): ?array
    {
        try {
            // Clean the response to extract JSON
            $jsonStart = strpos($response, '{');
            $jsonEnd = strrpos($response, '}');
            
            if ($jsonStart === false || $jsonEnd === false) {
                Log::error('Invalid JSON response from Gemini API');
                return null;
            }
            
            $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            $workoutData = json_decode($jsonString, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON parsing error', ['error' => json_last_error_msg()]);
                return null;
            }
            
            // Transform the AI response into our application's format
            return $this->transformToAppFormat($workoutData, $parameters);
            
        } catch (\Exception $e) {
            Log::error('Error parsing Gemini response', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Transform AI response to application format
     * 
     * @param array $workoutData
     * @param array $parameters
     * @return array
     */
    private function transformToAppFormat(array $workoutData, array $parameters): array
    {
        $transformedPlan = [
            'name' => $workoutData['plan_name'] ?? 'AI Generated Workout Plan',
            'description' => $workoutData['description'] ?? 'Generated by AI based on your preferences',
            'weeks_duration' => $parameters['weeks_duration'] ?? 4,
            'schedule' => []
        ];

        foreach ($workoutData['weeks'] ?? [] as $week) {
            $weekNumber = $week['week_number'] ?? 1;
            $transformedPlan['schedule'][$weekNumber] = [];
            
            foreach ($week['days'] ?? [] as $day) {
                $dayNumber = $day['day_number'] ?? 1;
                $transformedPlan['schedule'][$weekNumber][$dayNumber] = [];
                
                foreach ($day['exercises'] ?? [] as $exercise) {
                    $transformedPlan['schedule'][$weekNumber][$dayNumber][] = [
                        'exercise_name' => $exercise['name'] ?? 'Unknown Exercise',
                        'category' => $exercise['category'] ?? 'general',
                        'equipment' => $exercise['equipment'] ?? 'bodyweight',
                        'sets' => $exercise['sets'] ?? 3,
                        'reps' => $exercise['reps'] ?? '8-12',
                        'rest_time' => $exercise['rest_time'] ?? '90 seconds',
                        'notes' => $exercise['notes'] ?? '',
                        'workout_type' => $day['workout_type'] ?? 'General'
                    ];
                }
            }
        }

        return $transformedPlan;
    }

    /**
     * Get split description for the prompt
     * 
     * @param string $splitType
     * @return string
     */
    private function getSplitDescription(string $splitType): string
    {
        return match($splitType) {
            '2_on_1_off' => '2 days on, 1 day off (workout Monday, Tuesday, Thursday, Friday)',
            '3_on_1_off' => '3 days on, 1 day off (workout Monday, Tuesday, Wednesday, Friday, Saturday, Sunday)',
            '4_on_1_off' => '4 days on, 1 day off (workout Monday through Thursday, Saturday through Tuesday)',
            '5_on_2_off' => '5 days on, 2 days off (workout Monday through Friday)',
            '6_on_1_off' => '6 days on, 1 day off (workout Monday through Saturday)',
            'alternating' => 'Alternating days (workout Monday, Wednesday, Friday)',
            default => '2 days on, 1 day off (workout Monday, Tuesday, Thursday, Friday)'
        };
    }

    /**
     * Get goals description for the prompt
     * 
     * @param string $goals
     * @return string
     */
    private function getGoalsDescription(string $goals): string
    {
        return match($goals) {
            'strength' => 'Building strength and power',
            'muscle_gain' => 'Building muscle mass and size',
            'endurance' => 'Improving cardiovascular endurance',
            'weight_loss' => 'Fat loss and body composition',
            'general_fitness' => 'Overall fitness and health',
            'sports_performance' => 'Improving sports performance',
            'rehabilitation' => 'Rehabilitation and injury prevention',
            default => 'Overall fitness and health'
        };
    }

    /**
     * Get equipment description for the prompt
     * 
     * @param string $equipment
     * @return string
     */
    private function getEquipmentDescription(string $equipment): string
    {
        return match($equipment) {
            'gym' => 'Full gym with barbells, dumbbells, machines, and cables',
            'home_gym' => 'Home gym with limited equipment (dumbbells, resistance bands)',
            'bodyweight' => 'Bodyweight exercises only',
            'minimal' => 'Minimal equipment (resistance bands, small weights)',
            default => 'Full gym with barbells, dumbbells, machines, and cables'
        };
    }
} 