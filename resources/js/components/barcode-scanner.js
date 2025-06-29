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

            // Check if ZXing is available after loading
            if (typeof ZXing === "undefined") {
                throw new Error("Failed to load ZXing library");
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
            script.src =
                "https://unpkg.com/@zxing/library@0.20.0/umd/index.min.js";

            script.onload = () => {
                // Wait a bit for the library to initialize
                setTimeout(() => {
                    if (typeof ZXing !== "undefined") {
                        resolve();
                    } else {
                        reject(new Error("ZXing library failed to initialize"));
                    }
                }, 100);
            };

            script.onerror = () => {
                // Try fallback URL
                const fallbackScript = document.createElement("script");
                fallbackScript.src =
                    "https://cdn.jsdelivr.net/npm/@zxing/library@0.20.0/umd/index.min.js";

                fallbackScript.onload = () => {
                    setTimeout(() => {
                        if (typeof ZXing !== "undefined") {
                            resolve();
                        } else {
                            reject(
                                new Error("ZXing library failed to initialize")
                            );
                        }
                    }, 100);
                };

                fallbackScript.onerror = () => {
                    reject(
                        new Error(
                            "Failed to load ZXing library from both sources"
                        )
                    );
                };

                document.head.appendChild(fallbackScript);
            };

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
