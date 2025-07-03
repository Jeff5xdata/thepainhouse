/**
 * Barcode Scanner Component
 *
 * This class provides barcode scanning functionality using the ZXing library.
 * It can scan various barcode formats including QR codes, EAN, UPC, etc.
 * The scanner uses the device's camera to capture and decode barcodes.
 */
class BarcodeScanner {
    /**
     * Initialize the barcode scanner
     * @param {string} containerId - The ID of the HTML element that will contain the video stream
     * @param {function} onScan - Callback function called when a barcode is successfully scanned
     */
    constructor(containerId, onScan) {
        this.containerId = containerId; // ID of the container element
        this.onScan = onScan; // Callback function for successful scans
        this.codeReader = null; // ZXing code reader instance
        this.isScanning = false; // Flag to track scanning state
        this.videoElement = null; // Reference to the video element
    }

    /**
     * Initialize the barcode scanner and load required dependencies
     * @returns {Promise<boolean>} - True if initialization successful, false otherwise
     */
    async init() {
        try {
            // Load ZXing library dynamically if not already loaded
            if (typeof ZXing === "undefined") {
                await this.loadZXingLibrary();
            }

            // Verify ZXing library is available after loading
            if (typeof ZXing === "undefined") {
                throw new Error("Failed to load ZXing library");
            }

            // Create new ZXing code reader instance for multi-format barcode scanning
            this.codeReader = new ZXing.BrowserMultiFormatReader();

            // Get reference to the video container element
            this.videoElement = document.getElementById(this.containerId);

            // Validate that the container element exists
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

    /**
     * Dynamically load the ZXing library from CDN
     * Includes fallback URL in case the primary source fails
     * @returns {Promise} - Resolves when library is loaded, rejects on failure
     */
    async loadZXingLibrary() {
        return new Promise((resolve, reject) => {
            // Create script element for primary CDN source
            const script = document.createElement("script");
            script.src =
                "https://unpkg.com/@zxing/library@0.20.0/umd/index.min.js";

            // Handle successful load from primary source
            script.onload = () => {
                // Wait a bit for the library to initialize completely
                setTimeout(() => {
                    if (typeof ZXing !== "undefined") {
                        resolve();
                    } else {
                        reject(new Error("ZXing library failed to initialize"));
                    }
                }, 100);
            };

            // Handle failure from primary source - try fallback CDN
            script.onerror = () => {
                // Create script element for fallback CDN source
                const fallbackScript = document.createElement("script");
                fallbackScript.src =
                    "https://cdn.jsdelivr.net/npm/@zxing/library@0.20.0/umd/index.min.js";

                // Handle successful load from fallback source
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

                // Handle failure from both sources
                fallbackScript.onerror = () => {
                    reject(
                        new Error(
                            "Failed to load ZXing library from both sources"
                        )
                    );
                };

                // Append fallback script to document head
                document.head.appendChild(fallbackScript);
            };

            // Append primary script to document head
            document.head.appendChild(script);
        });
    }

    /**
     * Start barcode scanning using the device camera
     * @returns {Promise<boolean>} - True if scanning started successfully, false otherwise
     */
    async startScanning() {
        // Check if scanner is properly initialized and not already scanning
        if (!this.codeReader || this.isScanning) {
            return false;
        }

        try {
            this.isScanning = true;

            // Start video stream and begin scanning for barcodes
            await this.codeReader.decodeFromVideoDevice(
                null, // Use default camera (null = first available)
                this.videoElement, // Video element to display camera feed
                (result, error) => {
                    // Callback function for scan results
                    if (result) {
                        // Successful scan - call the onScan callback with the barcode text
                        this.onScan(result.text);
                        // Stop scanning after successful scan
                        this.stopScanning();
                    }
                    // Note: error parameter is ignored as we only care about successful scans
                }
            );

            return true;
        } catch (error) {
            console.error("Failed to start barcode scanning:", error);
            this.isScanning = false;
            return false;
        }
    }

    /**
     * Stop the barcode scanning process
     * This will stop the video stream and reset the scanner state
     */
    stopScanning() {
        if (this.codeReader && this.isScanning) {
            this.codeReader.reset(); // Reset the ZXing reader
            this.isScanning = false; // Update scanning state
        }
    }

    /**
     * Clean up resources and destroy the scanner instance
     * This should be called when the scanner is no longer needed
     */
    destroy() {
        this.stopScanning(); // Stop any active scanning
        if (this.codeReader) {
            this.codeReader.reset(); // Reset the ZXing reader
            this.codeReader = null; // Clear the reference
        }
    }
}

// Export the BarcodeScanner class globally for use in other modules
window.BarcodeScanner = BarcodeScanner;
