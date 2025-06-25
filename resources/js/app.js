import "./bootstrap";
import Alpine from "alpinejs";
import persist from "@alpinejs/persist";
import "./components/barcode-scanner.js";

// Only initialize Alpine if it hasn't been initialized yet
if (!window.Alpine) {
    Alpine.plugin(persist);
    window.Alpine = Alpine;
    Alpine.start();
}

// Notification system
document.addEventListener("DOMContentLoaded", function () {
    // Create notification container if it doesn't exist
    if (!document.getElementById("notification-container")) {
        const container = document.createElement("div");
        container.id = "notification-container";
        container.className = "fixed top-4 right-4 z-50 space-y-2";
        document.body.appendChild(container);
    }

    // Listen for notification events
    window.addEventListener("notify", function (event) {
        const { type, message } = event.detail;
        showNotification(type, message);
    });

    // Show notification function
    function showNotification(type, message) {
        const container = document.getElementById("notification-container");
        const notification = document.createElement("div");

        const baseClasses =
            "p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 ease-in-out";
        const typeClasses = {
            success: "bg-green-500 text-white",
            error: "bg-red-500 text-white",
            warning: "bg-yellow-500 text-white",
            info: "bg-blue-500 text-white",
        };

        notification.className = `${baseClasses} ${
            typeClasses[type] || typeClasses.info
        }`;
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
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        `;

        // Add slide-in animation
        notification.style.transform = "translateX(100%)";
        container.appendChild(notification);

        // Trigger animation
        setTimeout(() => {
            notification.style.transform = "translateX(0)";
        }, 10);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.transform = "translateX(100%)";
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
});
