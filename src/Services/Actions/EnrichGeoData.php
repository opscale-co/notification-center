<?php

namespace Opscale\NotificationCenter\Services\Actions;

use Illuminate\Support\Facades\Http;
use Opscale\Actions\Action;
use Opscale\NotificationCenter\Models\Event;

class EnrichGeoData extends Action
{
    public function identifier(): string
    {
        return 'enrich-geo-data';
    }

    public function name(): string
    {
        return 'Enrich Geo Data';
    }

    public function description(): string
    {
        return 'Enriches event payload with geolocation data based on IP address using ipgeolocation.io API.';
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'event_id',
                'description' => 'The event ID to enrich with geo data',
                'type' => 'string',
                'rules' => ['required', 'string', 'exists:notification_center_events,id'],
            ],
        ];
    }

    public function outputs(): array
    {
        return [
            [
                'name' => 'success',
                'description' => 'Whether the event was enriched successfully',
                'type' => 'boolean',
                'rules' => ['required', 'boolean'],
            ],
            [
                'name' => 'message',
                'description' => 'Reason the enrichment did not succeed, when applicable',
                'type' => 'string',
                'rules' => ['nullable', 'string'],
            ],
            [
                'name' => 'event_id',
                'description' => 'The event that was enriched',
                'type' => 'string',
                'rules' => ['nullable'],
            ],
            [
                'name' => 'geo',
                'description' => 'The resolved geolocation data',
                'type' => 'array',
                'rules' => ['nullable', 'array'],
            ],
        ];
    }

    public function handle(array $attributes = []): array
    {
        $event = Event::findOrFail($attributes['event_id']);
        $payload = $event->payload ?? [];

        $ip = $payload['ip'] ?? null;

        if (! $ip) {
            return [
                'success' => false,
                'message' => 'No IP address found in event payload',
            ];
        }

        $geoData = $this->fetchGeoData($ip);

        if (! $geoData) {
            return [
                'success' => false,
                'message' => 'Failed to fetch geo data',
            ];
        }

        $payload['geo'] = $geoData;
        $event->update(['payload' => $payload]);

        return [
            'success' => true,
            'event_id' => $event->id,
            'geo' => $geoData,
        ];
    }

    /**
     * Fetch geolocation data from ipgeolocation.io API.
     */
    protected function fetchGeoData(string $ip): ?array
    {
        $apiKey = config('notification-center.ipgeolocation.api_key');

        if (! $apiKey) {
            return null;
        }

        $response = Http::get('https://api.ipgeolocation.io/ipgeo', [
            'apiKey' => $apiKey,
            'ip' => $ip,
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }
}
