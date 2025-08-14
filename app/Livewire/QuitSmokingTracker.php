<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\PushNotificationService;

class QuitSmokingTracker extends Component
{
    use WithPagination;

    public $startDate;
    public $currentDate;
    public $targetQuitDate;
    public $cigarettesPerDay = 20;
    public $packPrice = 8.59;
    public $cigarettesPerPack = 20;
    public $smokingLogs = [];
    public $showAddLogModal = false;
    public $newLogTime;
    public $newLogCigarettes = 1;
    public $reductionPlan = [];
    public $totalCost = 0;
    public $totalCigarettes = 0;
    public $daysSmokeFree = 0;
    public $nextSmokeTime;
    public $showNotification = false;
    protected $notificationService;

    protected $paginationTheme = 'tailwind';
    
    // Add this to ensure proper pagination handling
    protected $queryString = ['page'];

    public function mount()
    {
        $this->startDate = now()->format('Y-m-d');
        $this->currentDate = now()->format('Y-m-d');
        $this->targetQuitDate = now()->addMonth()->format('Y-m-d');
        $this->loadSmokingLogs();
        $this->calculateReductionPlan();
        $this->calculateNextSmokeTime();
    }

    public function loadSmokingLogs()
    {
        // In a real app, this would load from database
        // For now, we'll use session storage
        $sessionLogs = session('smoking_logs', []);
        
        // Ensure we always have an array, not an object
        if (!is_array($sessionLogs)) {
            $sessionLogs = [];
        }
        
        $this->smokingLogs = $sessionLogs;
        $this->calculateTotals();
    }

    public function calculateReductionPlan()
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->targetQuitDate);
        $totalDays = $startDate->diffInDays($endDate);
        
        // Ensure reductionPlan is always an array
        $this->reductionPlan = [];
        $currentCigarettes = $this->cigarettesPerDay;
        
        for ($day = 0; $day <= $totalDays; $day++) {
            $date = $startDate->copy()->addDays($day);
            $progress = $day / $totalDays;
            
            // Gradual reduction: start at 20, end at 0
            $targetCigarettes = max(0, round($this->cigarettesPerDay * (1 - $progress)));
            
            $this->reductionPlan[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $day + 1,
                'target_cigarettes' => $targetCigarettes,
                'actual_cigarettes' => $this->getActualCigarettesForDate($date->format('Y-m-d')),
                'completed' => $this->isDayCompleted($date->format('Y-m-d')),
            ];
        }
    }

    public function getActualCigarettesForDate($date)
    {
        // Ensure smokingLogs is always an array
        if (!is_array($this->smokingLogs)) {
            $this->smokingLogs = [];
        }
        
        $dayLogs = array_filter($this->smokingLogs, function($log) use ($date) {
            return $log['date'] === $date;
        });
        
        return array_sum(array_column($dayLogs, 'cigarettes'));
    }

    public function isDayCompleted($date)
    {
        $actual = $this->getActualCigarettesForDate($date);
        $target = $this->getTargetCigarettesForDate($date);
        return $actual <= $target;
    }

    public function getTargetCigarettesForDate($date)
    {
        $startDate = Carbon::parse($this->startDate);
        $targetDate = Carbon::parse($date);
        $day = $startDate->diffInDays($targetDate);
        $totalDays = $startDate->diffInDays(Carbon::parse($this->targetQuitDate));
        $progress = $day / $totalDays;
        
        return max(0, round($this->cigarettesPerDay * (1 - $progress)));
    }

    public function addSmokingLog()
    {
        $this->validate([
            'newLogTime' => 'required',
            'newLogCigarettes' => 'required|integer|min:1|max:5',
        ]);

        $log = [
            'id' => uniqid(),
            'date' => $this->currentDate,
            'time' => $this->newLogTime,
            'cigarettes' => $this->newLogCigarettes,
            'timestamp' => now()->timestamp,
        ];

        // Ensure smokingLogs is always an array
        if (!is_array($this->smokingLogs)) {
            $this->smokingLogs = [];
        }
        
        $this->smokingLogs[] = $log;
        session(['smoking_logs' => $this->smokingLogs]);
        
        $this->calculateTotals();
        $this->calculateNextSmokeTime();
        $this->reset(['newLogTime', 'newLogCigarettes']);
        $this->showAddLogModal = false;
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Smoking log added successfully!'
        ]);
    }

    public function deleteSmokingLog($logId)
    {
        // Ensure smokingLogs is always an array
        if (!is_array($this->smokingLogs)) {
            $this->smokingLogs = [];
        }
        
        $this->smokingLogs = array_filter($this->smokingLogs, function($log) use ($logId) {
            return $log['id'] !== $logId;
        });
        
        session(['smoking_logs' => $this->smokingLogs]);
        $this->calculateTotals();
        $this->calculateNextSmokeTime();
        
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Smoking log deleted!'
        ]);
    }

    public function calculateTotals()
    {
        // Ensure smokingLogs is always an array
        if (!is_array($this->smokingLogs)) {
            $this->smokingLogs = [];
        }
        
        $this->totalCigarettes = array_sum(array_column($this->smokingLogs, 'cigarettes'));
        $this->totalCost = ($this->totalCigarettes / $this->cigarettesPerPack) * $this->packPrice;
        
        // Calculate days smoke free
        $lastSmokeDate = null;
        if (!empty($this->smokingLogs)) {
            $lastSmokeDate = max(array_column($this->smokingLogs, 'date'));
        }
        
        if ($lastSmokeDate) {
            $this->daysSmokeFree = Carbon::parse($lastSmokeDate)->diffInDays(now());
        } else {
            $this->daysSmokeFree = 0;
        }
    }

    public function calculateNextSmokeTime()
    {
        $today = $this->currentDate;
        $targetCigarettes = $this->getTargetCigarettesForDate($today);
        $actualCigarettes = $this->getActualCigarettesForDate($today);
        
        if ($actualCigarettes >= $targetCigarettes) {
            $this->nextSmokeTime = null;
            return;
        }
        
        // Calculate time intervals based on remaining cigarettes
        $remainingCigarettes = $targetCigarettes - $actualCigarettes;
        if ($remainingCigarettes <= 0) {
            $this->nextSmokeTime = null;
            return;
        }
        
        // Distribute remaining cigarettes throughout the day
        $hoursLeft = 24 - now()->hour;
        $interval = $hoursLeft / $remainingCigarettes;
        
        $nextTime = now()->addHours($interval);
        $this->nextSmokeTime = $nextTime->format('H:i');
        
        // Schedule push notification for next smoke time
        if ($remainingCigarettes > 0) {
            $this->scheduleSmokeNotification($nextTime, $remainingCigarettes);
        }
    }

    public function showSmokeNotification()
    {
        if ($this->nextSmokeTime) {
            $this->showNotification = true;
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => "Time to smoke! Your next cigarette is scheduled for {$this->nextSmokeTime}"
            ]);
        }
    }
    
    /**
     * Schedule a push notification for the next smoke time
     */
    public function scheduleSmokeNotification($nextTime, $remainingCigarettes)
    {
        try {
            $notificationService = app(PushNotificationService::class);
            
            $title = "🚬 Time to Smoke";
            $body = "You have {$remainingCigarettes} cigarette(s) remaining today. Next scheduled time: " . $nextTime->format('H:i');
            
            $data = [
                'type' => 'smoke_reminder',
                'remaining_cigarettes' => $remainingCigarettes,
                'next_time' => $nextTime->format('H:i'),
                'user_id' => Auth::id()
            ];
            
            $notificationService->scheduleNotification(
                Auth::id(),
                $title,
                $body,
                $nextTime,
                $data
            );
            
            // Also dispatch a Livewire event for immediate frontend notification
            $this->dispatch('show-push-notification', [
                'title' => $title,
                'body' => $body,
                'data' => [
                    'type' => $data['type'],
                    'remaining_cigarettes' => $data['remaining_cigarettes'],
                    'next_time' => $data['next_time'],
                    'user_id' => $data['user_id']
                ]
            ]);
            
            Log::info("Smoke notification scheduled for user " . Auth::id() . " at " . $nextTime->format('H:i:s'));
            
        } catch (\Exception $e) {
            Log::error("Failed to schedule smoke notification: " . $e->getMessage());
        }
    }

    public function updatingPage()
    {
        // Reset to first page when filters change
        $this->resetPage();
    }

    public function render()
    {
        $this->calculateReductionPlan();
        
        // Ensure smokingLogs is loaded
        if (empty($this->smokingLogs)) {
            $this->loadSmokingLogs();
        }
        
        // Convert to collection and sort safely, then manually paginate
        $sortedLogs = collect($this->smokingLogs)
            ->filter(function($log) {
                return is_array($log) && isset($log['timestamp']);
            })
            ->sortByDesc('timestamp');
            
        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $paginatedLogs = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedLogs->slice($offset, $perPage),
            $sortedLogs->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
        
        return view('livewire.quit-smoking-tracker', [
            'reductionPlan' => $this->reductionPlan,
            'smokingLogs' => $paginatedLogs,
        ]);
    }
}
