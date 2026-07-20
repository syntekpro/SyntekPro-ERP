const cacheName = 'erp-pos-shell-v1';
const cacheablePrefixes = ['/build/', '/css/', '/js/', '/images/', '/favicon'];
const offlinePaths = ['/', '/login', '/dashboard', '/pos/sales'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(cacheName).then((cache) => cache.addAll(offlinePaths)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(async (keys) => {
            await Promise.all(keys.filter((key) => key !== cacheName).map((key) => caches.delete(key)));
            await self.clients.claim();
        })
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET' || new URL(request.url).origin !== self.location.origin) {
        return;
    }

    if (request.url.includes('/api/')) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(request)
                .then((response) => {
                    if (!response || response.status !== 200) {
                        return response;
                    }

                    const shouldCache = cacheablePrefixes.some((prefix) => request.url.includes(prefix)) || request.mode === 'navigate';

                    if (shouldCache) {
                        const cloned = response.clone();
                        caches.open(cacheName).then((cache) => cache.put(request, cloned));
                    }

                    return response;
                })
                .catch(async () => {
                    if (request.mode === 'navigate') {
                        return caches.match('/pos/sales') || caches.match('/login') || Response.error();
                    }

                    return Response.error();
                });
        })
    );
});