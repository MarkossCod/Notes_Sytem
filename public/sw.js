// Cache minimo da instalacao PWA; altere a versao ao renovar estes recursos.
const CACHE_NAME = 'notessytem-v1';
const urlsToCache = [
    '/',
    '/manifest.json',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png'
];

// Prepara a pagina inicial, o manifesto e os icones para abertura rapida.
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
    );
});

// Usa o cache quando disponivel e recorre a rede para os demais pedidos.
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});