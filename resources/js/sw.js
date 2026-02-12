function urlBase64ToUint8Array(base64String) {
    var padding = '='.repeat((4 - base64String.length % 4) % 4);
    var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    var rawData = atob(base64);
    var outputArray = new Uint8Array(rawData.length);
    for (var i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

self.addEventListener('message', function (event) {
    if (event.data && event.data.action === 'subscribe') {
        var vapidPublicKey = event.data.vapidPublicKey;
        var registerUrl = event.data.registerUrl;

        self.registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
        }).then(function (subscription) {
            var key = subscription.getKey('p256dh');
            var token = subscription.getKey('auth');

            return fetch(registerUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                    key: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
                    token: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
                    content_encoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0],
                }),
            });
        }).then(function (response) {
            event.source.postMessage({
                success: response.ok,
            });
        }).catch(function (error) {
            event.source.postMessage({
                success: false,
                error: error.message,
            });
        });
    }
});

self.addEventListener('push', function (event) {
    var data = event.data ? event.data.json() : {};

    var title = data.title || 'Notification';
    var options = {
        body: data.body || '',
        icon: data.icon || '/favicon.png',
        badge: data.badge || '/favicon.png',
        data: {
            url: data.action || '/',
        },
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    var url = event.notification.data && event.notification.data.url
        ? event.notification.data.url
        : '/';

    event.waitUntil(
        self.clients.openWindow(url)
    );
});
