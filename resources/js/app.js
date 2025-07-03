import "./bootstrap";
import Alpine from "alpinejs";
import persist from "@alpinejs/persist";
import "./components/barcode-scanner.js";

// Initialize Alpine.js framework if it hasn't been initialized yet
// Alpine.js provides reactive functionality for the application
if (!window.Alpine) {
    // Add the persist plugin to Alpine.js for state persistence across page reloads
    Alpine.plugin(persist);
    // Make Alpine available globally
    window.Alpine = Alpine;
    // Start Alpine.js
    Alpine.start();
}

// Notification system for displaying user feedback
// This system handles success, error, warning, and info notifications
document.addEventListener("DOMContentLoaded", function () {
    // Create a notification container if it doesn't exist
    // This container will hold all notification elements
    if (!document.getElementById("notification-container")) {
        const container = document.createElement("div");
        container.id = "notification-container";
        // Position container in top-right corner with high z-index
        container.className = "fixed top-4 right-4 z-50 space-y-2";
        document.body.appendChild(container);
    }

    // Listen for custom notification events from other parts of the application
    // These events can be triggered using: window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Your message' } }))
    window.addEventListener("notify", function (event) {
        const { type, message } = event.detail;
        showNotification(type, message);
    });

    /**
     * Display a notification with the specified type and message
     * @param {string} type - The type of notification (success, error, warning, info)
     * @param {string} message - The message to display
     */
    function showNotification(type, message) {
        const container = document.getElementById("notification-container");
        const notification = document.createElement("div");

        // Base CSS classes for all notifications
        const baseClasses =
            "p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 ease-in-out";

        // Color classes for different notification types
        const typeClasses = {
            success: "bg-green-500 text-white", // Green background for success messages
            error: "bg-red-500 text-white", // Red background for error messages
            warning: "bg-yellow-500 text-white", // Yellow background for warning messages
            info: "bg-blue-500 text-white", // Blue background for info messages
        };

        // Apply appropriate classes based on notification type
        notification.className = `${baseClasses} ${
            typeClasses[type] || typeClasses.info
        }`;

        // Create notification HTML with appropriate icon and close button
        notification.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${
                            type === "success"
                                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />'
                                : ""
                        }
                        ${
                            type === "error"
                                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />'
                                : ""
                        }
                        ${
                            type === "warning"
                                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />'
                                : ""
                        }
                        ${
                            type === "info"
                                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
                                : ""
                        }
                    </svg>
                    <span>${message}</span>
                </div>
                <!-- Close button for manual dismissal -->
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        `;

        // Add slide-in animation from right side
        notification.style.transform = "translateX(100%)";
        container.appendChild(notification);

        // Trigger slide-in animation after a brief delay
        setTimeout(() => {
            notification.style.transform = "translateX(0)";
        }, 10);

        // Auto-remove notification after 5 seconds with slide-out animation
        setTimeout(() => {
            if (notification.parentElement) {
                // Start slide-out animation
                notification.style.transform = "translateX(100%)";
                // Remove element after animation completes
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
});
