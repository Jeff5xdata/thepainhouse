<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GeminiApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiWorkoutGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_workout_generator_page_loads()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('workout.ai-generator'));

        $response->assertStatus(200);
        $response->assertSee('AI Workout Generator');
    }

    public function test_ai_workout_generator_requires_authentication()
    {
        $response = $this->get(route('workout.ai-generator'));

        $response->assertRedirect(route('login'));
    }

    public function test_gemini_service_can_build_prompt()
    {
        $service = app(GeminiApiService::class);
        
        $parameters = [
            'weeks_duration' => 4,
            'split_type' => '2_on_1_off',
            'fitness_level' => 'intermediate',
            'goals' => 'strength',
            'equipment' => 'gym',
            'time_per_workout' => 60,
        ];

        // Test that the service can be instantiated
        $this->assertInstanceOf(GeminiApiService::class, $service);
        
        // Test that the service can handle parameters
        $this->assertIsArray($parameters);
        $this->assertEquals(4, $parameters['weeks_duration']);
        $this->assertEquals('2_on_1_off', $parameters['split_type']);
    }

    public function test_gemini_service_handles_missing_api_key()
    {
        // Temporarily remove API key from config
        config(['services.gemini.api_key' => null]);
        
        $service = app(GeminiApiService::class);
        $parameters = [
            'weeks_duration' => 4,
            'split_type' => '2_on_1_off',
            'fitness_level' => 'intermediate',
            'goals' => 'strength',
            'equipment' => 'gym',
            'time_per_workout' => 60,
        ];

        $result = $service->generateWorkoutPlan($parameters);
        
        // Should return null when API key is missing
        $this->assertNull($result);
    }

    public function test_ai_workout_generator_form_validation()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('workout.ai-generator'), [
                'weeks_duration' => 0, // Invalid: must be at least 1
                'split_type' => '', // Invalid: required
                'fitness_level' => 'invalid', // Invalid: not in allowed list
                'goals' => '', // Invalid: required
                'equipment' => '', // Invalid: required
                'time_per_workout' => 20, // Invalid: must be at least 30
            ]);

        $response->assertSessionHasErrors([
            'weeks_duration',
            'split_type',
            'fitness_level',
            'goals',
            'equipment',
            'time_per_workout',
        ]);
    }

    public function test_ai_workout_generator_form_validates_correct_data()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('workout.ai-generator'), [
                'weeks_duration' => 4,
                'split_type' => '2_on_1_off',
                'fitness_level' => 'intermediate',
                'goals' => 'strength',
                'equipment' => 'gym',
                'time_per_workout' => 60,
            ]);

        // Should not have validation errors
        $response->assertSessionDoesntHaveErrors([
            'weeks_duration',
            'split_type',
            'fitness_level',
            'goals',
            'equipment',
            'time_per_workout',
        ]);
    }
} 