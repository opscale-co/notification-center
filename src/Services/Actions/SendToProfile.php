<?php

namespace Opscale\NotificationCenter\Services\Actions;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Opscale\Actions\Action;
use Opscale\NotificationCenter\Jobs\ExecuteNotificationStrategy;
use Opscale\NotificationCenter\Models\Enums\NotificationStatus;
use Opscale\NotificationCenter\Models\Notification;
use Opscale\NotificationCenter\Models\Profile;

class SendToProfile extends Action
{
    public function identifier(): string
    {
        return 'send-to-profile';
    }

    public function name(): string
    {
        return 'Send to Profile';
    }

    public function description(): string
    {
        return 'Sends a notification directly to a single profile, marking it as published and dispatching the delivery strategy.';
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'notification',
                'description' => 'The notification to send',
                'type' => Notification::class,
                'rules' => ['required'],
            ],
            [
                'name' => 'profile_id',
                'description' => 'The profile to send the notification to',
                'type' => 'string',
                'rules' => ['required', Rule::exists((new Profile)->getTable(), 'id')],
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
            'profile_id' => fn (): array => Profile::with('notifiable')->get()
                ->mapWithKeys(fn (Profile $profile): array => [
                    $profile->id => $profile->notifiable?->name ?? $profile->id,
                ])->all(),
        ];
    }

    public function handle(array $attributes = []): array
    {
        $notification = $this->notification($attributes);

        if (! $notification instanceof Notification) {
            return $this->fail('Notification not found.');
        }

        $profile = Profile::find($attributes['profile_id'] ?? null);

        if (! $profile) {
            return $this->fail('Invalid profile selected.');
        }

        $notification->update([
            'status' => NotificationStatus::PUBLISHED,
        ]);

        ExecuteNotificationStrategy::dispatch($notification, $profile);

        return $this->succeed([
            'success' => true,
            'message' => 'Notification(s) sent to profile successfully!',
            'notification_id' => $notification->id,
        ]);
    }

    public function outputs(): array
    {
        return [
            [
                'name' => 'success',
                'description' => 'Whether the notification was sent successfully',
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
                'description' => 'The notification that was sent',
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
