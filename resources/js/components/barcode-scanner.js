// Barcode Scanner Component
class BarcodeScanner {
    constructor(containerId, onScan) {
        this.containerId = containerId;
        this.onScan = onScan;
        this.codeReader = null;
        this.isScanning = false;
        this.videoElement = null;
    }

    async init() {
        try {
            // Load ZXing library dynamically
            if (typeof ZXing === "undefined") {
                await this.loadZXingLibrary();
            }

            this.codeReader = new ZXing.BrowserMultiFormatReader();
            this.videoElement = document.getElementById(this.containerId);

            if (!this.videoElement) {
                throw new Error(
                    `Container with id '${this.containerId}' not found`
                );
            }

            return true;
        } catch (error) {
            console.error("Failed to initialize barcode scanner:", error);
            return false;
        }
    }

    async loadZXingLibrary() {
        return new Promise((resolve, reject) => {
            const script = document.createElement("script");
            script.src = "https://unpkg.com/@zxing/library@latest";
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async startScanning() {
        if (!this.codeReader || this.isScanning) {
            return false;
        }

        try {
            this.isScanning = true;

            await this.codeReader.decodeFromVideoDevice(
                null,
                this.videoElement,
                (result, error) => {
                    if (result) {
                        this.onScan(result.text);
                        this.stopScanning();
                    }
                }
            );

            return true;
        } catch (error) {
            console.error("Failed to start barcode scanning:", error);
            this.isScanning = false;
            return false;
        }
    }

    stopScanning() {
        if (this.codeReader && this.isScanning) {
            this.codeReader.reset();
            this.isScanning = false;
        }
    }

    destroy() {
        this.stopScanning();
        if (this.codeReader) {
            this.codeReader.reset();
            this.codeReader = null;
        }
    }
}

// Export for use in other modules
window.BarcodeScanner = BarcodeScanner;
