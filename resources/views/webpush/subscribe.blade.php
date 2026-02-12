<x-mail::message>
# {{ __('Push Notifications') }}

{{ __('Enable push notifications to stay updated with the latest alerts and messages.') }}

<x-mail::button id="subscribe-btn" url="#">
{{ __('Enable Push Notifications') }}
</x-mail::button>

<div id="status" style="margin-top: 16px; font-size: 14px;"></div>

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
</x-mail::message>

<script>
const statusEl = document.getElementById('status');
const subscribeBtn = document.getElementById('subscribe-btn');

async function subscribeToPush() {
    try {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            statusEl.textContent = '{{ __("Push notifications are not supported in this browser.") }}';
            return;
        }

        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            statusEl.textContent = '{{ __("Notification permission was denied.") }}';
            return;
        }

        await navigator.serviceWorker.register('{{ route("notification-center.webpush.sw") }}');
        const registration = await navigator.serviceWorker.ready;

        navigator.serviceWorker.addEventListener('message', function (event) {
            if (event.data.success) {
                statusEl.textContent = '{{ __("Successfully subscribed to push notifications!") }}';
                subscribeBtn.style.display = 'none';
            } else {
                statusEl.textContent = '{{ __("Failed to register subscription. Please try again.") }}';
            }
        });

        registration.active.postMessage({
            action: 'subscribe',
            vapidPublicKey: '{{ $vapidPublicKey }}',
            registerUrl: '{{ $registerUrl }}',
        });
    } catch (error) {
        statusEl.textContent = '{{ __("An error occurred:") }} ' + error.message;
    }
}

if (subscribeBtn) {
    subscribeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        subscribeToPush();
    });
}
</script>
