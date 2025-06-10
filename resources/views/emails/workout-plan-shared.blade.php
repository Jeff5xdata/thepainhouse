@component('mail::message')
# Workout Plan Shared with You

Someone has shared a workout plan with you from The Pain House!

## Plan Details
**Name:** {{ $workoutPlan->name }}
**Created by:** {{ $workoutPlan->user->name }}

You can view this workout plan until {{ $expiresAt }}.

@component('mail::button', ['url' => route('workout-plans.shared', $shareLink->token)])
View Workout Plan
@endcomponent

## Why Join The Pain House?
- Create and customize your own workout routines
- Track your progress and achievements

@component('mail::button', ['url' => $registerUrl])
Create Free Account
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent 