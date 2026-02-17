<x-mail::message>
# {{ __('Push Notifications') }}

{{ __('Enable push notifications to stay updated with the latest alerts and messages.') }}

<button id="subscribe-btn" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; border-radius: 4px; color: #fff; display: inline-block; overflow: hidden; text-decoration: none; background-color: #e8bc2c; border: none; padding: 8px 18px; font-size: 15px; font-weight: 600; cursor: pointer;">{{ __('Enable Push Notifications') }}</button>

<div id="status" style="margin-top: 16px; margin-bottom: 16px; font-size: 14px;"></div>

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
</x-mail::message>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusEl = document.getElementById('status');
    const subscribeBtn = document.getElementById('subscribe-btn');

    async function subscribeToPush() {
        try {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                statusEl.textContent = '{{ __("Push notifications are not supported in this browser.") }}';
                return;
            }

            await navigator.serviceWorker.register('{{ $swUrl }}');
            const registration = await navigator.serviceWorker.ready;

            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                statusEl.textContent = '{{ __("Notification permission was denied.") }}';
                return;
            }

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

    subscribeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        subscribeToPush();
    });
});
</script>
