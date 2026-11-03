<?php

namespace Opscale\NotificationCenter\Services\Actions;

use Illuminate\Support\Collection;
use Opscale\Actions\Action;
use Opscale\NotificationCenter\Models\Audience;
use Opscale\NotificationCenter\Models\Enums\AudienceType;
use Opscale\NotificationCenter\Models\Profile;

class CreateAudience extends Action
{
    public function identifier(): string
    {
        return 'create-audience';
    }

    public function name(): string
    {
        return 'Create Audience';
    }

    public function description(): string
    {
        return 'Creates a static audience from the selected profiles and attaches them as its members.';
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'name',
                'description' => 'The name of the audience',
                'type' => 'string',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'description',
                'description' => 'A description for the audience',
                'type' => 'text',
                'rules' => ['nullable', 'string', 'max:500'],
            ],
            [
                // System-supplied: the selected Profile records. Prefilled from
                // the Nova selection, so it is hidden from every input surface.
                'name' => 'profiles',
                'description' => 'The profiles to add to the audience',
                'type' => 'array',
                'rules' => ['nullable'],
            ],
        ];
    }

    public function prefill(): array
    {
        return ['profiles' => fn ($ctx) => $ctx?->models];
    }

    public function handle(array $attributes = []): array
    {
        $profiles = $this->profiles($attributes);

        $audience = Audience::create([
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'type' => AudienceType::STATIC,
            'total_members' => $profiles->count(),
        ]);

        $audience->profiles()->attach($profiles->pluck('id'));

        return $this->succeed([
            'success' => true,
            'message' => $profiles->count() . ' profile(s) added to audience "' . $audience->name . '".',
            'audience_id' => $audience->id,
        ]);
    }

    public function outputs(): array
    {
        return [
            [
                'name' => 'success',
                'description' => 'Whether the audience was created successfully',
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
                'name' => 'audience_id',
                'description' => 'The created audience',
                'type' => 'string',
                'rules' => ['nullable'],
            ],
        ];
    }

    /**
     * Resolve the selected profiles. In Nova they arrive as a Collection from
     * the bulk selection; empty otherwise (this action is a Nova bulk action).
     */
    private function profiles(array $attributes): Collection
    {
        $profiles = $attributes['profiles'] ?? null;

        if ($profiles instanceof Collection) {
            return $profiles;
        }

        return Profile::query()->whereRaw('1 = 0')->get();
    }
}
