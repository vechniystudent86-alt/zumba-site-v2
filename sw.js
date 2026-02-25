// Service Worker for Zumba SPB
const CACHE_VERSION = 'v1.0.0';
const STATIC_CACHE = `static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `dynamic-${CACHE_VERSION}`;
const IMAGE_CACHE = `images-${CACHE_VERSION}`;

// Resources to cache immediately
const STATIC_ASSETS = [
  '/',
  '/index.html',
  '/styles.min.css',
  '/responsive.min.css',
  '/script.js',
  '/hero-photo.webp',
  '/hero-photo.png',
  '/favicon-32x32.png',
  '/favicon-16x16.png',
  '/icon-192x192.png',
  '/icon-512x512.png',
  '/offline.html'
];

// INSTALL - Cache static assets
self.addEventListener('install', (event) => {
  console.log('[SW] Installing Service Worker...');
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => {
        console.log('[SW] Caching static assets');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => self.skipWaiting())
  );
});

// ACTIVATE - Clean old caches
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating Service Worker...');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames
          .filter(name => name.startsWith('static-') || 
                         name.startsWith('dynamic-') || 
                         name.startsWith('images-'))
          .filter(name => !name.includes(CACHE_VERSION))
          .map(name => {
            console.log('[SW] Deleting old cache:', name);
            return caches.delete(name);
          })
      );
    }).then(() => self.clients.claim())
  );
});

// FETCH - Handle requests with different strategies
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') return;
  
  // Skip external requests (except APIs we need)
  if (!url.origin.startsWith(self.location.origin)) {
    // Allow Yandex Maps and Metrika
    if (url.origin.includes('yandex.') || url.origin.includes('telegram.org')) {
      return;
    }
    return;
  }
  
  // Navigation requests (HTML pages) - Network First
  if (request.mode === 'navigate') {
    event.respondWith(networkFirst(request, '/offline.html'));
    return;
  }
  
  // Images - Cache First
  if (request.destination === 'image') {
    event.respondWith(cacheFirst(request, IMAGE_CACHE, '/hero-photo.png'));
    return;
  }
  
  // CSS, JS - Stale While Revalidate
  if (request.destination === 'style' || request.destination === 'script') {
    event.respondWith(staleWhileRevalidate(request, STATIC_CACHE));
    return;
  }
  
  // Default - Stale While Revalidate
  event.respondWith(staleWhileRevalidate(request, DYNAMIC_CACHE));
});

// CACHE FIRST Strategy (for images)
async function cacheFirst(request, cacheName, fallbackUrl) {
  const cachedResponse = await caches.match(request);
  if (cachedResponse) {
    return cachedResponse;
  }
  
  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const cache = await caches.open(cacheName);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    const fallback = await caches.match(fallbackUrl);
    return fallback || new Response('Offline', { status: 503 });
  }
}

// NETWORK FIRST Strategy (for navigation)
async function networkFirst(request, fallbackUrl) {
  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    const cachedResponse = await caches.match(request);
    return cachedResponse || caches.match(fallbackUrl) || 
           new Response('Offline', { status: 503 });
  }
}

// STALE WHILE REVALIDATE Strategy (for CSS/JS)
async function staleWhileRevalidate(request, cacheName) {
  const cache = await caches.open(cacheName);
  const cachedResponse = await cache.match(request);
  
  const fetchPromise = fetch(request).then(networkResponse => {
    if (networkResponse.ok) {
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  }).catch(() => null);
  
  return cachedResponse || fetchPromise;
}

// Handle skip waiting message from main thread
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
