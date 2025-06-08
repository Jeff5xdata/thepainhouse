<?php

namespace App\Livewire;

use Livewire\Component;

class DarkModeToggle extends Component
{
    public bool $darkMode = false;

    public function mount()
    {
        // Initialize dark mode from localStorage via JavaScript
        $this->darkMode = false; // Default value, will be updated by JavaScript
    }

    public function toggleDarkMode()
    {
        $this->darkMode = !$this->darkMode;
        
        // Dispatch event to update Alpine.js state
        $this->dispatch('dark-mode-toggled', darkMode: $this->darkMode);
    }

    public function render()
    {
        return view('livewire.dark-mode-toggle');
    }
} 