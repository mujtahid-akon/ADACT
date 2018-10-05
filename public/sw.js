/**
 * SW.js - Service Worker
 */

importScripts('/cache-polyfill.min.js');


self.addEventListener('install', function(e) {
    e.waitUntil(
        caches.open('ADACT').then(function(cache) {
            return cache.addAll([
                '/js/app.min.js',
                '/js/creative.min.js',
                '/js/nav-side.min.js',
                '/js/pure-swipe.min.js',
                '/css/main.min.css',
                '/css/style.min.css',
                '/css/creative.min.css',
                '/css/components-md.min.css',
                '/css/nav-side.min.css',
                '/logos/ADACT_Logo@24x.png',
                '/logos/favicon.ico',
                '/logos/dna.jpg',
            ]);
        })
    );
});

self.addEventListener('fetch', function(event) {
    console.log(event.request.url);
    event.respondWith(
        caches.match(event.request).then(function(response) {
            return response || fetch(event.request);
        })
    );
});