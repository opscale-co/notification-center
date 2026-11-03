<?php

namespace Opscale\NotificationCenter\Services\Actions;

use Illuminate\Support\Collection;
use Opscale\Actions\Action;
use Opscale\NotificationCenter\Models\Delivery;

class ForceDelivery extends Action
{
    public function identifier(): string
    {
        return 'force-delivery';
    }

    public function name(): string
    {
        return 'Force Delivery';
    }

    public function description(): string
    {
        return 'Re-sends a delivery through its configured channel using the notification class mapped in notification-center.messages.';
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'delivery',
                'description' => 'The delivery to force',
                'type' => Delivery::class,
                'rules' => ['required'],
            ],
        ];
    }

    public function prefill(): array
    {
        return ['delivery' => fn ($ctx) => $ctx?->models?->first()];
    }

    public function handle(array $attributes = []): array
    {
        $delivery = $this->delivery($attributes);

        if (! $delivery instanceof Delivery) {
            return $this->fail('Delivery not found.');
        }

        $messages = config('notification-center.messages', []);
        $channel = $delivery->channel;

        if (! isset($messages[$channel])) {
            return $this->fail("No notification class configured for channel: {$channel}");
        }

        $profile = $delivery->profile;

        if (! $profile) {
            return $this->fail('Profile not found for this delivery.');
        }

        $notificationClass = $messages[$channel];
        $profile->notify(new $notificationClass($delivery));

        return $this->succeed([
            'success' => true,
            'message' => 'Notification(s) sent successfully!',
            'delivery_id' => $delivery->id,
        ]);
    }

    public function outputs(): array
    {
        return [
            [
                'name' => 'success',
                'description' => 'Whether the delivery was forced successfully',
                'type' => 'boolean',
                'rules' => ['required', 'boolean'],
            ],
            [
                'name' => 'message',
                'description' => 'User-facing result message',
                'type' => 'string',
                'rules' => ['nullable', 'string'],
            ],
            [
                'name' => 'delivery_id',
                'description' => 'The delivery that was forced',
                'type' => 'string',
                'rules' => ['nullable'],
            ],
        ];
    }

    /**
     * Resolve the delivery from the Nova selection (instance | Collection) or
     * a plain id (API/CLI/MCP).
     */
    private function delivery(array $attributes): ?Delivery
    {
        $delivery = $attributes['delivery'] ?? null;

        if ($delivery instanceof Collection) {
            $delivery = $delivery->first();
        }

        if ($delivery instanceof Delivery) {
            return $delivery;
        }

        return is_string($delivery) || is_int($delivery)
            ? Delivery::query()->find($delivery)
            : null;
    }
}
