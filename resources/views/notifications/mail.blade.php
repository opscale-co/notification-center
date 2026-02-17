@if (config('notification-center.google_analytics_id'))
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('notification-center.google_analytics_id') }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ config("notification-center.google_analytics_id") }}');
</script>
@endif

<x-mail::message>
# {{ $notification->subject }}

{!! $notification->body !!}

@if ($actionUrl)
<x-mail::button :url="$actionUrl">
{{ __('View') }}
</x-mail::button>
@endif

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
</x-mail::message>
