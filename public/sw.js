// Cache minimo da instalacao PWA; altere a versao ao renovar estes recursos.
const CACHE_NAME = 'notessytem-v2';
const urlsToCache = [
    '/',
    '/manifest.json?v=2',
    '/icons/notessytem-logo-192.png?v=2',
    '/icons/notessytem-logo-512.png?v=2'
];

// Prepara a pagina inicial, o manifesto e os icones para abertura rapida.
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
    );
});

// Remove versões antigas para que a logo atualizada não permaneça presa no navegador.
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(names => Promise.all(
            names.filter(name => name !== CACHE_NAME).map(name => caches.delete(name))
        ))
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
