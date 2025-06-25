@component('mail::message')
# New Message from {{ $sender->name }}

You have received a new message from **{{ $sender->name }}**!

## Message Details
**Subject:** {{ $message->subject }}  
**From:** {{ $sender->name }} ({{ $sender->email }})  
**Sent:** {{ $message->created_at->format('F j, Y \a\t g:i A') }}

## Message Content
{{ $message->content }}

@component('mail::button', ['url' => $loginUrl])
View Message in App
@endcomponent

## Need to respond?
Log in to your account to send a reply and continue the conversation.

Thanks,<br>
{{ config('app.name') }}
@endcomponent 