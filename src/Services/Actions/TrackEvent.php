<?php

namespace Opscale\NotificationCenter\Services\Actions;

use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log as Logger;
use Opscale\Actions\Action;
use Opscale\NotificationCenter\Models\Delivery;
use Opscale\NotificationCenter\Models\Enums\DeliveryStatus;
use Opscale\NotificationCenter\Models\Event;
use Opscale\NotificationCenter\Notifications\Notification;

class TrackEvent extends Action
{
    /**
     * The delivery instance for fluent tracking.
     */
    protected ?Delivery $delivery = null;

    public function identifier(): string
    {
        return 'track-event';
    }

    public function name(): string
    {
        return 'Track Event';
    }

    public function description(): string
    {
        return 'Tracks delivery events for notifications including Sent, Received, Opened, Verified, Failed, and Expired statuses.';
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'delivery_id',
                'description' => 'The delivery ID to track the event for',
                'type' => 'string',
                'rules' => ['required', 'string', 'exists:deliveries,id'],
            ],
            [
                'name' => 'event',
                'description' => 'The event type to track',
                'type' => 'string',
                'rules' => ['required', 'string', 'in:Sent,Received,Opened,Verified,Failed,Expired,custom'],
            ],
            [
                'name' => 'payload',
                'description' => 'Additional payload data for the event',
                'type' => 'array',
                'rules' => ['nullable', 'array'],
            ],
        ];
    }

    public function handle(array $attributes = []): array
    {
        $this->delivery = Delivery::findOrFail($attributes['delivery_id']);
        $event = $attributes['event'];
        $payload = $attributes['payload'] ?? [];

        match ($event) {
            'Sent' => $this->sent(),
            'Received' => $this->received($payload),
            'Opened' => $this->opened($payload),
            'Verified' => $this->verified($payload),
            'Failed' => $this->failed($payload),
            'Expired' => $this->expired(),
            default => $this->custom($event, $payload),
        };

        return [
            'success' => true,
            'delivery_id' => $this->delivery->id,
            'event' => $event,
        ];
    }

    /**
     * Log an event for the delivery.
     */
    public function logEvent(string $event, array $payload = []): Event
    {
        return Event::create([
            'delivery_id' => $this->delivery->id,
            'name' => $event,
            'payload' => $payload,
        ]);
    }

    /**
     * Mark the delivery as sent.
     */
    public function sent(): static
    {
        $this->logEvent(__(DeliveryStatus::SENT->value), [
            'sent_at' => now()->toIso8601String(),
        ]);

        $this->updateStatus(DeliveryStatus::SENT);

        return $this;
    }

    /**
     * Mark the delivery as received.
     */
    public function received(array $payload = []): static
    {
        $this->logEvent(__(DeliveryStatus::RECEIVED->value), array_merge($payload, [
            'received_at' => now()->toIso8601String(),
        ]));

        $this->updateStatus(DeliveryStatus::RECEIVED);

        return $this;
    }

    /**
     * Mark the delivery as opened.
     */
    public function opened(array $payload = []): static
    {
        $this->logEvent(__(DeliveryStatus::OPENED->value), array_merge($payload, [
            'opened_at' => now()->toIso8601String(),
        ]));

        $this->updateStatus(DeliveryStatus::OPENED);

        return $this;
    }

    /**
     * Mark the delivery as verified/read.
     */
    public function verified(array $payload = []): static
    {
        $this->logEvent(__(DeliveryStatus::VERIFIED->value), array_merge($payload, [
            'verified_at' => now()->toIso8601String(),
        ]));

        $this->updateStatus(DeliveryStatus::VERIFIED);

        return $this;
    }

    /**
     * Mark the delivery as failed.
     */
    public function failed(array $payload = []): static
    {
        $this->logEvent(__(DeliveryStatus::FAILED->value), array_merge($payload, [
            'failed_at' => now()->toIso8601String(),
        ]));

        $this->updateStatus(DeliveryStatus::FAILED);

        return $this;
    }

    /**
     * Mark the delivery as expired.
     */
    public function expired(): static
    {
        $this->logEvent(__(DeliveryStatus::EXPIRED->value), [
            'expired_at' => now()->toIso8601String(),
        ]);

        $this->updateStatus(DeliveryStatus::EXPIRED);

        return $this;
    }

    /**
     * Track a custom event.
     */
    public function custom(string $event, array $payload = []): static
    {
        $this->logEvent($event, array_merge($payload, [
            'tracked_at' => now()->toIso8601String(),
        ]));

        return $this;
    }

    /**
     * Handle as event listener.
     */
    public function asListener(NotificationSent|NotificationFailed $event): void
    {
        if (! $event->notification instanceof Notification) {
            return;
        }

        $this->delivery = $event->notification->getDelivery();

        match (true) {
            $event instanceof NotificationSent => $this->sent(),
            $event instanceof NotificationFailed => $this->handleFailed($event),
        };
    }

    /**
     * Update the delivery status if the transition is allowed.
     */
    protected function updateStatus(DeliveryStatus $newStatus): void
    {
        $currentStatus = $this->delivery->status;

        if (! $currentStatus->canTransitionTo($newStatus)) {
            Logger::warning('Invalid delivery status transition attempted', [
                'delivery_id' => $this->delivery->id,
                'current_status' => $currentStatus->value,
                'attempted_status' => $newStatus->value,
            ]);

            return;
        }

        $this->delivery->update(['status' => $newStatus]);
    }

    /**
     * Handle the NotificationFailed event.
     */
    protected function handleFailed(NotificationFailed $event): void
    {
        $this->failed([
            'error' => $event->data['exception']?->getMessage() ?? 'Unknown error',
        ]);

        $type = $this->delivery->notification->type->value ?? null;

        if ($type === 'Alert') {
            Logger::critical('Critical notification failed', [
                'notification_id' => $this->delivery->notification->id,
                'delivery_id' => $this->delivery->id,
                'channel' => $this->delivery->channel,
                'error' => $event->data['exception']?->getMessage(),
            ]);
        }
    }
}
