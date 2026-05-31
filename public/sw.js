const cacheName = 'keyfleet-v7';
// Only cache the essential offline page or the root
const assetsToCache = [
    '/',
    '/manifest.json',
    '/icons/icon.png'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(cacheName).then(cache => {
            // We use a map to catch errors individually so one failure doesn't kill the SW
            return Promise.allSettled(
                assetsToCache.map(url => cache.add(url))
            );
        })
    );
    self.skipWaiting(); // Activate immediately
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== cacheName)
                    .map(key => caches.delete(key))
            );
        })
    );
    self.clients.claim(); // Take control immediately
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});

self.addEventListener('push', (event) => {
    if (!event.data) return;

    let data = {};
    let notificationUrl = '/pwa-login';
    
    try {
        data = event.data.json();
        console.log('Full push data received:', data);
        
        // Try different locations where the URL might be
        if (data.data && data.data.url) {
            notificationUrl = data.data.url;
        } else if (data.url) {
            notificationUrl = data.url;
        } else if (data.click_action) {
            notificationUrl = data.click_action;
        } else if (data.action_url) {
            notificationUrl = data.action_url;
        }
        
        // Make URL absolute if it's relative
        if (notificationUrl && !notificationUrl.startsWith('http')) {
            notificationUrl = 'https://staging.keyfleethub.com' + notificationUrl;
        }
        
        console.log('Final URL to use:', notificationUrl);
        
    } catch (e) {
        data = { title: "Keyfleet", body: event.data.text() };
        console.error('Parse error:', e);
    }

    const options = {
        body: data.body || 'Gumagana na ang push notifications mo!',
        icon: '/icons/icon.png',
        badge: '/icons/icon.png',
        data: { 
            url: notificationUrl,
            timestamp: Date.now()
        },
        contentAvailable: true,
        mutableContent: true,
        requireInteraction: true // Keeps notification visible
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'Keyfleet', options)
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    
    // Get URL from notification data
    let urlToOpen = event.notification.data?.url || 'https://staging.keyfleethub.com/pwa-login';
    
    // Ensure URL is absolute
    if (urlToOpen && !urlToOpen.startsWith('http')) {
        urlToOpen = 'https://staging.keyfleethub.com' + urlToOpen;
    }
    
    console.log('Notification clicked, opening URL:', urlToOpen);
    
    // Handle the navigation
    event.waitUntil(
        (async () => {
            try {
                // Get all window clients
                const allClients = await clients.matchAll({
                    type: 'window',
                    includeUncontrolled: true
                });
                
                let existingClient = null;
                
                // Look for existing client
                for (const client of allClients) {
                    if (client.url === urlToOpen || client.url.includes('/pwa-login')) {
                        existingClient = client;
                        break;
                    }
                }
                
                // If found existing client, focus and navigate if needed
                if (existingClient) {
                    if (existingClient.url !== urlToOpen) {
                        await existingClient.navigate(urlToOpen);
                    }
                    await existingClient.focus();
                } else {
                    // Open new window
                    await clients.openWindow(urlToOpen);
                }
            } catch (error) {
                console.error('Navigation error:', error);
                // Fallback: try simple open
                try {
                    await clients.openWindow(urlToOpen);
                } catch (fallbackError) {
                    console.error('Fallback error:', fallbackError);
                }
            }
        })()
    );
});