<?php

namespace App\Livewire;

use Livewire\Component;

class DarkModeToggle extends Component
{
    public bool $darkMode = false;

    public function mount()
    {
        // Get the dark mode state from session or localStorage
        $this->darkMode = session('darkMode', false);
        
        // Dispatch initial state to Alpine
        $this->dispatch('dark-mode-toggled', darkMode: $this->darkMode);
    }

    public function toggleDarkMode()
    {
        $this->darkMode = !$this->darkMode;
        
        // Persist state in session
        session(['darkMode' => $this->darkMode]);
        
        // Dispatch to Alpine
        $this->dispatch('dark-mode-toggled', darkMode: $this->darkMode);
    }

    public function render()
    {
        return view('livewire.dark-mode-toggle');
    }
} 