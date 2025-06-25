@component('mail::message')
# Trainer Request from {{ $client->name }}

You have received a trainer request from **{{ $client->name }}**!

## Request Details
**Client Name:** {{ $client->name }}  
**Client Email:** {{ $client->email }}  
**Message:** {{ $trainerRequest->message ?: 'No additional message provided.' }}

@component('mail::button', ['url' => $loginUrl])
Login to Respond
@endcomponent

## What happens next?
1. Log in to your account
2. Check your messaging center for the request
3. Accept or decline the request
4. Start managing your new client's fitness journey

## Don't have an account yet?
If you're not already registered with The Pain House, you can create a free account to start managing clients.

@component('mail::button', ['url' => $registerUrl])
Create Free Account
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent 