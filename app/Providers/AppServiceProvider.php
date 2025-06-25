<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Livewire\DarkModeToggle;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

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

        // Add route model binding for WorkoutSession
        Route::bind('workoutSession', function ($value) {
            \Log::info('Route model binding called', [
                'value' => $value,
                'user_id' => auth()->id(),
            ]);
            
            $session = \App\Models\WorkoutSession::where('id', $value)
                ->where('user_id', auth()->id())
                ->first();
                
            \Log::info('Route model binding result', [
                'found' => $session ? true : false,
                'session_id' => $session->id ?? null,
            ]);
            
            return $session ?? abort(404);
        });
    }
}
