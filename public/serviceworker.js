var staticCacheName = "hl-v" + new Date().getTime();
var filesToCache = [
    "/offline",
    "/build/assets/app.css",
    "/build/assets/app.js",
    "/favicon.ico",
];

// Cache on install
self.addEventListener("install", (event) => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName).then((cache) => {
            // Cache each file individually to handle failures gracefully
            return Promise.allSettled(
                filesToCache.map((file) =>
                    cache.add(file).catch((err) => {
                        console.error("Failed to cache:", file, err);
                        return Promise.resolve(); // Continue despite error
                    })
                )
            );
        })
    );
});

// Clear cache on activate
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((cacheName) => cacheName.startsWith("hl-"))
                    .filter((cacheName) => cacheName !== staticCacheName)
                    .map((cacheName) => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", (event) => {
    event.respondWith(
        caches
            .match(event.request)
            .then((response) => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match("/offline");
            })
    );
});
