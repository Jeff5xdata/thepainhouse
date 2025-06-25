<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Message;
use App\Models\TrainerRequest;
use App\Models\User;

class MessagingCenter extends Component
{
    public $messages = [];
    public $incomingTrainerRequests = [];
    public $outgoingTrainerRequests = [];
    public $selectedMessage = null;
    public $selectedConversation = null;
    public $selectedTrainerRequest = null;
    public $newMessage = [
        'recipient_id' => '',
        'subject' => '',
        'content' => ''
    ];
    public $isLoading = false;
    public $activeTab = 'messages'; // 'messages', 'incoming-requests', or 'outgoing-requests'

    public function mount()
    {
        $this->loadMessages();
        $this->loadTrainerRequests();
    }

    public function loadMessages()
    {
        $user = auth()->user();
        
        // Get messages where user is recipient
        $this->messages = Message::where('recipient_id', $user->id)
            ->with(['sender'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function loadTrainerRequests()
    {
        $user = auth()->user();
        
        // Get incoming trainer requests (where user is the trainer)
        $this->incomingTrainerRequests = TrainerRequest::where('trainer_email', $user->email)
            ->where('status', 'pending')
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        
        // Get outgoing trainer requests (where user is the client)
        $this->outgoingTrainerRequests = TrainerRequest::where('client_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function selectMessage($messageId)
    {
        $this->selectedMessage = Message::with('sender')->find($messageId);
    }

    public function selectConversation($userId)
    {
        $user = auth()->user();
        
        $messages = Message::where(function($query) use ($user, $userId) {
            $query->where('sender_id', $user->id)
                  ->where('recipient_id', $userId);
        })->orWhere(function($query) use ($user, $userId) {
            $query->where('sender_id', $userId)
                  ->where('recipient_id', $user->id);
        })->with(['sender', 'recipient'])
          ->orderBy('created_at', 'asc')
          ->get();
        
        $otherUser = User::find($userId);
        
        $this->selectedConversation = [
            'other_user' => $otherUser,
            'messages' => $messages
        ];
    }

    public function replyToTrainerRequest($trainerEmail)
    {
        $user = auth()->user();
        
        // Find the trainer by email
        $trainer = User::where('email', $trainerEmail)->first();
        
        if (!$trainer) {
            session()->flash('error', 'Trainer not found in the system.');
            return;
        }
        
        // Get conversation with this trainer
        $messages = Message::where(function($query) use ($user, $trainer) {
            $query->where('sender_id', $user->id)
                  ->where('recipient_id', $trainer->id);
        })->orWhere(function($query) use ($user, $trainer) {
            $query->where('sender_id', $trainer->id)
                  ->where('recipient_id', $user->id);
        })->with(['sender', 'recipient'])
          ->orderBy('created_at', 'asc')
          ->get();
        
        $this->selectedConversation = [
            'other_user' => $trainer,
            'messages' => $messages
        ];
    }

    public function sendMessage()
    {
        $this->validate([
            'newMessage.recipient_id' => 'required|exists:users,id',
            'newMessage.subject' => 'required|string|max:255',
            'newMessage.content' => 'required|string|max:5000',
        ]);

        $this->isLoading = true;

        try {
            Message::create([
                'sender_id' => auth()->id(),
                'recipient_id' => $this->newMessage['recipient_id'],
                'subject' => $this->newMessage['subject'],
                'content' => $this->newMessage['content'],
                'is_read' => false,
            ]);

            $this->reset('newMessage');
            $this->loadMessages();
            session()->flash('message', 'Message sent successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while sending the message.');
        }

        $this->isLoading = false;
    }

    public function acceptTrainerRequest($requestId)
    {
        try {
            $trainerRequest = TrainerRequest::findOrFail($requestId);
            $user = auth()->user();

            // Check if the user is the intended trainer
            if ($user->email !== $trainerRequest->trainer_email) {
                session()->flash('error', 'Unauthorized action.');
                return;
            }

            // Update request status
            $trainerRequest->update([
                'status' => 'accepted',
                'responded_at' => now(),
            ]);

            // Assign trainer to client
            $client = $trainerRequest->client;
            $client->update(['my_trainer' => $user->id]);

            // Mark user as trainer if not already
            if (!$user->is_trainer) {
                $user->update(['is_trainer' => true]);
            }

            // Add a message to the client's message center about the approval
            Message::create([
                'sender_id' => $user->id,
                'recipient_id' => $client->id,
                'subject' => 'Trainer Request Approved',
                'content' => 'Your trainer request has been approved! You can now start working with your trainer.',
                'is_read' => false,
            ]);

            $this->loadTrainerRequests();
            session()->flash('message', 'Trainer request accepted!');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while accepting the request.');
        }
    }

    public function declineTrainerRequest($requestId)
    {
        try {
            $trainerRequest = TrainerRequest::findOrFail($requestId);
            $user = auth()->user();

            // Check if the user is the intended trainer
            if ($user->email !== $trainerRequest->trainer_email) {
                session()->flash('error', 'Unauthorized action.');
                return;
            }

            $trainerRequest->update([
                'status' => 'declined',
                'responded_at' => now(),
            ]);

            // Add a message to the client's message center about the rejection
            Message::create([
                'sender_id' => $user->id,
                'recipient_id' => $trainerRequest->client_id,
                'subject' => 'Trainer Request Declined',
                'content' => 'Your trainer request has been declined. You can try requesting another trainer.',
                'is_read' => false,
            ]);

            $this->loadTrainerRequests();
            session()->flash('message', 'Trainer request declined.');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while declining the request.');
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.messaging-center');
    }
} 