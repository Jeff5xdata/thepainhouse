# AI Workout Generator Feature

This feature adds AI-powered workout plan generation to The Pain House application using Google's Gemini API. Users can generate personalized workout plans based on their preferences, fitness level, and goals.

## Features

-   **AI-Powered Generation**: Uses Google's Gemini API to create personalized workout plans
-   **Customizable Parameters**:
    -   Plan duration (1-52 weeks)
    -   Workout split patterns (2 days on/1 off, 3 days on/1 off, etc.)
    -   Fitness level (beginner, intermediate, advanced)
    -   Primary goals (strength, muscle gain, endurance, weight loss, etc.)
    -   Available equipment (full gym, home gym, bodyweight, minimal)
    -   Time per workout (30-180 minutes)
-   **Smart Exercise Matching**: Automatically matches AI-generated exercises with existing exercises in the database
-   **Preview Before Save**: Users can preview the generated plan before saving it
-   **Seamless Integration**: Generated plans integrate directly with the existing workout system

## Setup Instructions

### 1. Environment Configuration

Add the Gemini API key to your `.env` file:

```env
GEMINI_API_KEY=your_gemini_api_key_here
```

You can get a Gemini API key by:

1. Going to [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Creating a new API key
3. Copying the key to your `.env` file

### 2. Service Configuration

The Gemini API configuration is already added to `config/services.php`:

```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
],
```

### 3. Access the Feature

1. Navigate to the main menu (hamburger icon)
2. Click on "AI Workout Generator"
3. Fill in your preferences
4. Click "Generate Workout Plan"
5. Preview the generated plan
6. Click "Save Workout Plan" to add it to your account

## How It Works

### 1. User Input Processing

The system collects user preferences through a form with the following options:

-   **Plan Duration**: 1-52 weeks
-   **Split Type**:

    -   2 Days On, 1 Day Off
    -   3 Days On, 1 Day Off
    -   4 Days On, 1 Day Off
    -   5 Days On, 2 Days Off
    -   6 Days On, 1 Day Off
    -   Alternating Days (Mon, Wed, Fri)

-   **Fitness Level**: Beginner, Intermediate, Advanced
-   **Goals**: Strength & Power, Muscle Gain, Endurance, Weight Loss, General Fitness, Sports Performance, Rehabilitation
-   **Equipment**: Full Gym, Home Gym, Bodyweight Only, Minimal Equipment
-   **Time per Workout**: 30-180 minutes

### 2. AI Prompt Generation

The system builds a detailed prompt for the Gemini API that includes:

-   All user preferences
-   Specific formatting requirements for the response
-   Guidelines for exercise selection and progression
-   Equipment considerations

### 3. Response Processing

The AI response is parsed and transformed into the application's format:

-   Extracts JSON from the AI response
-   Validates the structure
-   Transforms exercises to match the database schema
-   Handles exercise matching and creation

### 4. Exercise Matching

The system intelligently matches AI-generated exercises with existing exercises:

1. **Exact Match**: Tries to find exercises with the same name
2. **Partial Match**: Looks for exercises with similar names
3. **Auto-Create**: Creates new exercises if no match is found

### 5. Plan Integration

Generated plans are saved as active workout plans and integrate seamlessly with:

-   Workout Planner
-   Workout Sessions
-   Progress Tracking
-   Exercise Management

## API Integration

### GeminiApiService

The main service class handles all AI interactions:

```php
namespace App\Services;

class GeminiApiService
{
    // Generate workout plan using AI
    public function generateWorkoutPlan(array $parameters): ?array

    // Build the prompt for workout generation
    private function buildWorkoutPrompt(array $parameters): string

    // Parse the AI response into structured data
    private function parseWorkoutResponse(string $response, array $parameters): ?array

    // Transform AI response to application format
    private function transformToAppFormat(array $workoutData, array $parameters): array
}
```

### API Endpoint

The Gemini API endpoint used:

```
https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent
```

### Request Format

The service sends structured prompts to the AI with:

-   User preferences and constraints
-   Specific JSON response format requirements
-   Exercise guidelines and best practices
-   Equipment and fitness level considerations

## Components

### AiWorkoutGenerator (Livewire Component)

Main component that handles:

-   User input collection and validation
-   AI service integration
-   Plan preview and saving
-   Error handling and user feedback

### Features:

-   Real-time form validation
-   Loading states during generation
-   Modal preview of generated plans
-   Seamless integration with existing workout system

## Error Handling

The system includes comprehensive error handling:

1. **API Key Validation**: Checks if Gemini API key is configured
2. **Network Errors**: Handles API request failures gracefully
3. **Response Parsing**: Validates AI response structure
4. **Database Errors**: Handles exercise creation and plan saving errors
5. **User Feedback**: Provides clear error messages to users

## Security Considerations

-   API keys are stored securely in environment variables
-   All user input is validated and sanitized
-   Generated exercises are created under the user's account
-   No sensitive data is sent to the AI service

## Performance Optimizations

-   Efficient JSON parsing and validation
-   Smart exercise matching to reduce database queries
-   Optimized database transactions for plan saving
-   Caching-ready architecture for future improvements

## Future Enhancements

Potential improvements for the AI workout generator:

-   **Exercise Image Generation**: Generate exercise demonstration images
-   **Progressive Overload**: AI-generated progression schemes
-   **Injury Prevention**: AI recommendations for injury prevention
-   **Nutrition Integration**: Combined workout and nutrition plans
-   **Personalization Learning**: AI that learns from user feedback
-   **Multiple Plan Options**: Generate multiple plan variations
-   **Seasonal Adjustments**: Weather and season-based modifications

## Troubleshooting

### Common Issues

1. **"Failed to generate workout plan"**

    - Check if Gemini API key is configured correctly
    - Verify internet connection
    - Check API rate limits

2. **"No exercises found"**

    - Ensure you have exercises in your database
    - Check exercise matching logic

3. **"Invalid response format"**
    - AI response parsing error
    - Check API response structure

### Debug Information

Enable debug logging by checking Laravel logs:

```bash
tail -f storage/logs/laravel.log
```

Look for entries with:

-   `GeminiApiService`
-   `AiWorkoutGenerator`
-   `AI workout generation`

## API Rate Limits

The Gemini API has rate limits that vary by plan:

-   **Free Tier**: Limited requests per minute
-   **Paid Plans**: Higher rate limits

The service includes built-in error handling for rate limit exceeded errors.

## Environment Variables

Make sure these environment variables are configured:

-   `GEMINI_API_KEY`: Your API key from Google AI Studio

## Testing

To test the AI workout generator:

1. Ensure you have a valid Gemini API key
2. Navigate to the AI Workout Generator page
3. Fill in test parameters
4. Generate a workout plan
5. Verify the plan is created correctly
6. Check that exercises are properly matched/created

## Support

For support and questions:

-   Check the application logs for detailed error messages
-   Verify your Gemini API key is valid and has sufficient quota
-   Ensure all required environment variables are set
-   Test with different parameter combinations

---

**AI Workout Generator** - Transform your fitness journey with AI-powered personalized workout plans that adapt to your goals, equipment, and schedule.
