# Trainer Request and Messaging System

This document describes the comprehensive trainer request and management system that has been added to The Pain House application.

## Recent Updates (June 2025)

### Bug Fixes and Improvements

-   **Fixed Layout Issue**: Resolved `Undefined variable $slot` error by properly structuring the component layout
-   **Enhanced Error Handling**: Improved trainer request error handling with graceful email failure recovery
-   **Message Center Integration**: Trainer requests now automatically create messages in the trainer's message center if they exist as users
-   **Database Constraint Fixes**: Resolved NOT NULL constraint violations for message subjects
-   **Authentication Improvements**: Switched from HTTP API calls to direct method calls for better reliability
-   **Trainer Request Status Tracking**: Added comprehensive status tracking in message center with Pending/Approved/Rejected badges
-   **Enhanced Messaging Center**: Updated with three-tab interface for messages, incoming requests, and outgoing requests

## Features

### 1. Trainer Request System

-   **Request a Trainer**: Users can request a trainer by email address
-   **Email Notifications**: Trainers receive email notifications when someone requests them
-   **Message Center Integration**: If the trainer exists as a user, the request automatically appears in their message center
-   **Status Tracking**: Clients can track their trainer requests with real-time status updates (Pending/Approved/Rejected)
-   **Accept/Decline**: Trainers can accept or decline requests through the messaging center
-   **Automatic Assignment**: When accepted, the trainer is automatically assigned to the client
-   **Robust Error Handling**: Requests succeed even if email delivery fails
-   **Self-Tracking**: Clients receive messages in their own message center for all trainer request activities

### 2. Messaging System

-   **Internal Messaging**: Trainers and clients can communicate through the messaging center
-   **Email Notifications**: Users receive email notifications for new messages
-   **Conversation View**: Full conversation history between users
-   **Unread Message Count**: Visual indicators for unread messages
-   **Trainer Request Messages**: Automatic message creation for trainer requests
-   **Three-Tab Interface**: Separate tabs for Messages, Incoming Requests, and My Requests
-   **Status Badges**: Visual status indicators for trainer request states

### 3. Trainer Dashboard

-   **Client Management**: Trainers can view all their assigned clients
-   **Client Statistics**: Overview of client progress including workout and nutrition data
-   **Quick Actions**: Send messages, create workouts, and view detailed progress
-   **Recent Activity**: Latest workouts and nutrition logs for each client

### 4. Client Progress Tracking

-   **Comprehensive Overview**: Current workout, weekly nutrition, and overall progress
-   **Progress Statistics**: Workout consistency, total workouts, average daily calories
-   **History Access**: Links to full workout and nutrition history
-   **Communication Tools**: Direct messaging and workout creation capabilities

## Database Structure

### New Tables

#### `trainer_requests`

-   `id` - Primary key
-   `client_id` - Foreign key to users table (the person requesting)
-   `trainer_email` - Email address of the requested trainer
-   `status` - Enum: 'pending', 'accepted', 'declined'
-   `message` - Optional message from the client
-   `responded_at` - Timestamp when trainer responded
-   `created_at`, `updated_at` - Timestamps

#### `messages`

-   `id` - Primary key
-   `sender_id` - Foreign key to users table
-   `recipient_id` - Foreign key to users table
-   `subject` - Message subject (required)
-   `content` - Message content
-   `is_read` - Boolean flag for read status
-   `read_at` - Timestamp when message was read
-   `created_at`, `updated_at` - Timestamps

### Updated User Model

-   Added relationships for trainer requests and messages
-   Added helper methods for checking trainer status (`isTrainer()`, `hasTrainer()`)
-   Added unread message count functionality (`unreadMessagesCount()`)

## Routes

### Web Routes

-   `GET /trainer/request` - Trainer request form (Livewire component)
-   `GET /messaging` - Messaging center (Livewire component)
-   `GET /trainer/dashboard` - Trainer dashboard (Livewire component)
-   `GET /trainer/client/{clientId}/progress` - Client progress details (Livewire component)

### API Routes

-   `POST /api/trainer-requests` - Create trainer request
-   `POST /api/trainer-requests/{id}/accept` - Accept trainer request
-   `POST /api/trainer-requests/{id}/decline` - Decline trainer request
-   `GET /api/trainer-requests/pending` - Get pending requests
-   `GET /api/messages` - Get user messages
-   `POST /api/messages` - Send message
-   `GET /api/messages/{id}` - Get specific message
-   `POST /api/messages/{id}/read` - Mark message as read
-   `GET /api/messages/unread/count` - Get unread count
-   `GET /api/messages/conversation/{userId}` - Get conversation with user

## Email Templates

### Trainer Request Email

-   Sent to trainer when someone requests them
-   Includes client information and optional message
-   Links to login/register pages
-   Graceful handling of email delivery failures

### New Message Email

-   Sent when someone receives a new message
-   Includes message content and sender information
-   Link to view message in the app

## Usage Flow

### For Clients

1. Navigate to "Request Trainer" in the menu
2. Enter trainer's email address and optional message
3. Submit request
4. System creates trainer request and message (if trainer exists)
5. **NEW**: Client receives message in their own message center with "Pending" status
6. Trainer receives email notification (if mail is configured)
7. **NEW**: Client can track request status in "My Requests" tab
8. Once accepted, can communicate through messaging center

### For Trainers

1. Receive email notification of trainer request (if mail is configured)
2. Check messaging center for pending requests and messages
3. **NEW**: View incoming requests in "Incoming Requests" tab
4. Accept or decline requests through the interface
5. **NEW**: System automatically notifies client of decision via message center
6. Access trainer dashboard to manage clients
7. View client progress and send messages

## Message Center Features

### Three-Tab Interface

1. **Messages Tab**

    - Regular messages between users
    - Conversation view with reply functionality
    - Unread message indicators

2. **Incoming Requests Tab** (for trainers)

    - Pending trainer requests from clients
    - Accept/Decline buttons
    - Client information and request messages
    - Request timestamps

3. **My Requests Tab** (for clients)
    - All trainer requests sent by the user
    - Status badges: Pending (Yellow), Approved (Green), Rejected (Red)
    - Request and response timestamps
    - Original request messages

### Status Tracking

-   **Pending**: Yellow badge, awaiting trainer response
-   **Approved**: Green badge, trainer-client relationship established
-   **Rejected**: Red badge, request declined by trainer

### Automatic Notifications

-   **Request Created**: Client gets "Trainer Request Status: Pending" message
-   **Request Approved**: Client gets "Trainer Request Approved" message
-   **Request Rejected**: Client gets "Trainer Request Declined" message

## Error Handling and Reliability

### Robust Error Handling

-   **Email Failures**: Trainer requests succeed even if email delivery fails
-   **Database Constraints**: Proper handling of required fields and relationships
-   **Validation**: Comprehensive validation for all inputs
-   **Logging**: Detailed error logging for debugging

### Graceful Degradation

-   **Mail Configuration**: System works with or without proper mail configuration
-   **Queue Workers**: No dependency on queue workers for basic functionality
-   **Network Issues**: HTTP failures don't prevent core functionality

## Security Features

-   **Authorization**: Only trainers can access trainer dashboard
-   **Client Access Control**: Trainers can only view their assigned clients
-   **Request Validation**: Prevents duplicate requests and self-requests
-   **Email Verification**: Uses existing email verification system
-   **CSRF Protection**: Built-in CSRF protection for all forms

## Navigation Integration

The system integrates seamlessly with the existing navigation:

-   "Request Trainer" appears for users without a trainer
-   "Messages" appears for all users with unread count indicator
-   "Trainer Dashboard" appears for users marked as trainers
-   Conditional display based on user roles and relationships

## Technical Implementation

### Livewire Components

-   `TrainerRequestForm` - Form for requesting trainers with direct method calls and message center integration
-   `MessagingCenter` - Complete messaging interface with three-tab layout and real-time updates
-   `TrainerDashboard` - Trainer management interface
-   `ClientProgress` - Detailed client progress view

### Controllers

-   `TrainerRequestController` - Handles trainer request logic via API with message center integration
-   `MessageController` - Handles messaging functionality via API

### Models

-   `TrainerRequest` - Trainer request model with relationships and scopes
-   `Message` - Message model with read/unread functionality and scopes
-   Updated `User` model with new relationships and helper methods

### Layout System

-   **Component Layout**: Properly structured Blade component layout
-   **Livewire Integration**: Seamless integration with Livewire components
-   **Responsive Design**: Mobile-friendly interface using Tailwind CSS

## Troubleshooting

### Common Issues and Solutions

1. **"Undefined variable $slot" Error**

    - **Solution**: Fixed by properly structuring component layout with `@props` and `{{ $slot }}`

2. **"An error occurred while sending the request"**

    - **Solution**: Implemented direct method calls instead of HTTP requests and added comprehensive error handling

3. **Email Notifications Not Working**

    - **Solution**: System continues to work even if email fails; check mail configuration in `.env`

4. **Database Constraint Violations**

    - **Solution**: Added required fields (subject) to message creation

5. **Trainer Request Status Not Updating**

    - **Solution**: Check message center "My Requests" tab for real-time status updates

## Future Enhancements

Potential improvements for the system:

-   Real-time messaging with WebSockets
-   File attachments in messages
-   Group messaging for multiple clients
-   Advanced progress analytics
-   Automated workout recommendations
-   Integration with external calendar systems
-   Push notifications for mobile apps
-   Video call integration for virtual training sessions
-   Bulk trainer request management
-   Trainer request templates and quick responses
