// Smoking Notifications Component
// Handles automatic notifications for smoking times and progress tracking

class SmokingNotifications {
    constructor() {
        this.notificationInterval = null;
        this.smokingSchedule = [];
        this.isInitialized = false;
        this.init();
    }

    init() {
        if (this.isInitialized) return;
        
        // Check if we're on the quit smoking page
        if (window.location.pathname.includes('/quit-smoking')) {
            this.setupNotifications();
            this.isInitialized = true;
        }
    }

    setupNotifications() {
        // Request notification permission
        this.requestNotificationPermission();
        
        // Set up periodic checks for smoking times
        this.startNotificationChecks();
        
        // Listen for Livewire events
        this.listenToLivewireEvents();
    }

    async requestNotificationPermission() {
        if ('Notification' in window) {
            if (Notification.permission === 'default') {
                await Notification.requestPermission();
            }
        }
    }

    startNotificationChecks() {
        // Check every 5 minutes for smoking times
        this.notificationInterval = setInterval(() => {
            this.checkSmokingTime();
        }, 5 * 60 * 1000);
        
        // Initial check
        this.checkSmokingTime();
    }

    checkSmokingTime() {
        // Get the next smoke time from the page
        const nextSmokeTimeElement = document.querySelector('[data-next-smoke-time]');
        if (!nextSmokeTimeElement) return;

        const nextSmokeTime = nextSmokeTimeElement.textContent.trim();
        if (!nextSmokeTime || nextSmokeTime === 'Done for today!') return;

        const [hours, minutes] = nextSmokeTime.split(':').map(Number);
        const now = new Date();
        const nextTime = new Date();
        nextTime.setHours(hours, minutes, 0, 0);

        // If it's time to smoke (within 5 minutes)
        const timeDiff = Math.abs(nextTime - now);
        if (timeDiff <= 5 * 60 * 1000) {
            this.showSmokingReminder();
        }
    }

    showSmokingReminder() {
        // Show browser notification
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('🚭 Time to Smoke!', {
                body: 'It\'s time for your scheduled cigarette. Stay on track with your reduction plan!',
                icon: '/favicon.ico',
                tag: 'smoking-reminder',
                requireInteraction: true
            });
        }

        // Show in-page notification
        this.showInPageNotification();
        
        // Play a subtle sound (optional)
        this.playNotificationSound();
    }

    showInPageNotification() {
        // Create a floating notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-orange-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 animate-bounce';
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">Time to smoke! Stay on track with your reduction plan.</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-orange-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 10000);
    }

    playNotificationSound() {
        // Create a simple beep sound
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.frequency.value = 800;
        oscillator.type = 'sine';

        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.5);
    }

    listenToLivewireEvents() {
        // Listen for Livewire events
        document.addEventListener('livewire:load', () => {
            // Listen for smoking log updates
            Livewire.on('smoking-log-updated', () => {
                this.updateSmokingSchedule();
            });
        });
    }

    updateSmokingSchedule() {
        // Update the smoking schedule based on current data
        // This would be called when smoking logs are updated
        setTimeout(() => {
            this.checkSmokingTime();
        }, 1000);
    }

    // Method to manually trigger a notification (for testing)
    triggerTestNotification() {
        this.showSmokingReminder();
    }

    // Cleanup method
    destroy() {
        if (this.notificationInterval) {
            clearInterval(this.notificationInterval);
        }
        this.isInitialized = false;
    }
}

// Initialize the component when the page loads
document.addEventListener('DOMContentLoaded', () => {
    window.smokingNotifications = new SmokingNotifications();
});

// Export for use in other modules
export default SmokingNotifications;
