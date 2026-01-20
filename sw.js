const CACHE_NAME = 'mines-payroll-v1';
const ASSETS = [
  '/',
  '/index.php',
  '/assets/css/auth.css',
  '/assets/logos/nes.jpg'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS);
    })
  );
});

self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request).then((response) => {
      return response || fetch(event.request);
    })
  );
});
