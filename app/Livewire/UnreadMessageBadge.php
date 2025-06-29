<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class UnreadMessageBadge extends Component
{
    public $unreadCount = 0;

    public function mount()
    {
        $this->updateUnreadCount();
    }

    public function updateUnreadCount()
    {
        $this->unreadCount = auth()->user()->unreadMessagesCount();
    }

    #[On('message-sent')]
    #[On('message-read')]
    #[On('message-received')]
    public function refresh()
    {
        $this->updateUnreadCount();
    }

    public function render()
    {
        return view('livewire.unread-message-badge');
    }
}
