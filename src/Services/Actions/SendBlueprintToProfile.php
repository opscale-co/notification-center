<?php

namespace Opscale\NotificationCenter\Services\Actions;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Opscale\Actions\Action;
use Opscale\NotificationCenter\Jobs\ExecuteNotificationStrategy;
use Opscale\NotificationCenter\Models\Blueprint;
use Opscale\NotificationCenter\Models\Enums\NotificationStatus;
use Opscale\NotificationCenter\Models\Notification;
use Opscale\NotificationCenter\Models\Profile;

class SendBlueprintToProfile extends Action
{
    public function identifier(): string
    {
        return 'send-blueprint-to-profile';
    }

    public function name(): string
    {
        return 'Send to Profile';
    }

    public function description(): string
    {
        return 'Builds a notification from a blueprint and its variable values, then sends it to a single profile.';
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'blueprint',
                'description' => 'The blueprint to build the notification from',
                'type' => Blueprint::class,
                'rules' => ['required'],
            ],
            [
                'name' => 'profile_id',
                'description' => 'The profile to send the notification to',
                'type' => 'string',
                'rules' => ['required', Rule::exists((new Profile)->getTable(), 'id')],
            ],
            [
                'name' => 'variables',
                'description' => 'Fill in a value for each dynamic variable detected in the blueprint.',
                'type' => 'array',
                'rules' => ['nullable'],
            ],
        ];
    }

    public function prefill(): array
    {
        return ['blueprint' => fn ($ctx) => $ctx?->models?->first()];
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

    /**
     * Seed the `variables` KeyValue field with one empty entry per dynamic
     * variable detected in the selected blueprint, re-creating the original
     * Nova action's `variablesPrefill()`. `getActionFields()` runs in HTTP
     * context, so `request()->resourceId` (the blueprint on its detail view)
     * is available here — unlike the context-free `handle()`.
     */
    public function getActionFields(): array
    {
        $fields = parent::getActionFields();

        $blueprint = Blueprint::find(request()->resourceId);
        $defaults = $blueprint ? array_fill_keys($blueprint->variables(), '') : [];

        foreach ($fields as $field) {
            if ($field->attribute === 'variables') {
                $field->default($defaults);
            }
        }

        return $fields;
    }

    public function handle(array $attributes = []): array
    {
        $blueprint = $this->blueprint($attributes);

        if (! $blueprint instanceof Blueprint) {
            return $this->fail('Blueprint not found.');
        }

        $profile = Profile::find($attributes['profile_id'] ?? null);

        if (! $profile) {
            return $this->fail('Invalid profile selected.');
        }

        $values = $attributes['variables'] ?? [];

        // Nova submits the KeyValue as a JSON string; decode defensively.
        if (is_string($values)) {
            $values = json_decode($values, true) ?: [];
        }

        try {
            $notification = Notification::create($blueprint->toNotificationAttributes($values) + [
                'status' => NotificationStatus::PUBLISHED,
                'blueprint_id' => $blueprint->id,
            ]);
        } catch (ValidationException $exception) {
            return $this->fail('The resulting notification is invalid: ' . $exception->getMessage());
        }

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
                'description' => 'Whether the notification was built and sent successfully',
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
                'description' => 'The notification that was created and sent',
                'type' => 'string',
                'rules' => ['nullable'],
            ],
        ];
    }

    /**
     * Resolve the blueprint from the Nova selection (instance | Collection) or
     * a plain id (API/CLI/MCP).
     */
    private function blueprint(array $attributes): ?Blueprint
    {
        $blueprint = $attributes['blueprint'] ?? null;

        if ($blueprint instanceof Collection) {
            $blueprint = $blueprint->first();
        }

        if ($blueprint instanceof Blueprint) {
            return $blueprint;
        }

        return is_string($blueprint) || is_int($blueprint)
            ? Blueprint::query()->find($blueprint)
            : null;
    }
}
