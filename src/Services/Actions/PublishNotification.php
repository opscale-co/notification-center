<?php

namespace Opscale\NotificationCenter\Services\Actions;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Opscale\Actions\Action;
use Opscale\NotificationCenter\Jobs\ExecuteNotificationStrategy;
use Opscale\NotificationCenter\Models\Audience;
use Opscale\NotificationCenter\Models\Enums\NotificationStatus;
use Opscale\NotificationCenter\Models\Notification;

class PublishNotification extends Action
{
    public function identifier(): string
    {
        return 'publish-notification';
    }

    public function name(): string
    {
        return 'Publish Notification';
    }

    public function description(): string
    {
        return 'Publishes a notification to an audience, marking it as published and dispatching the delivery strategy.';
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'notification',
                'description' => 'The notification to publish',
                'type' => Notification::class,
                'rules' => ['required'],
            ],
            [
                'name' => 'audience_id',
                'description' => 'The audience to send the notification to',
                'type' => 'string',
                'rules' => ['required', Rule::exists((new Audience)->getTable(), 'id')],
            ],
        ];
    }

    public function prefill(): array
    {
        return ['notification' => fn ($ctx) => $ctx?->models?->first()];
    }

    public function options(): array
    {
        return [
            'audience_id' => fn (): array => Audience::query()->pluck('name', 'id')->all(),
        ];
    }

    public function handle(array $attributes = []): array
    {
        $notification = $this->notification($attributes);

        if (! $notification instanceof Notification) {
            return $this->fail('Notification not found.');
        }

        $audience = Audience::find($attributes['audience_id'] ?? null);

        if (! $audience) {
            return $this->fail('Invalid audience selected.');
        }

        $notification->update([
            'status' => NotificationStatus::PUBLISHED,
        ]);

        $notification->audiences()->attach($audience->id);

        ExecuteNotificationStrategy::dispatch($notification);

        return $this->succeed([
            'success' => true,
            'message' => 'Notification(s) published successfully!',
            'notification_id' => $notification->id,
        ]);
    }

    public function outputs(): array
    {
        return [
            [
                'name' => 'success',
                'description' => 'Whether the notification was published successfully',
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
                'name' => 'notification_id',
                'description' => 'The notification that was published',
                'type' => 'string',
                'rules' => ['nullable'],
            ],
        ];
    }

    /**
     * Resolve the notification from the Nova selection (instance | Collection)
     * or a plain id (API/CLI/MCP).
     */
    private function notification(array $attributes): ?Notification
    {
        $notification = $attributes['notification'] ?? null;

        if ($notification instanceof Collection) {
            $notification = $notification->first();
        }

        if ($notification instanceof Notification) {
            return $notification;
        }

        return is_string($notification) || is_int($notification)
            ? Notification::query()->find($notification)
            : null;
    }
}
