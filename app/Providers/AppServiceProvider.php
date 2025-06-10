<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Livewire\DarkModeToggle;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('dark-mode-toggle', DarkModeToggle::class);
        Livewire::component('dashboard', Dashboard::class);

        // Register the guest layout component
        Blade::component('guest', \App\View\Components\Layouts\Guest::class);
    }
}
