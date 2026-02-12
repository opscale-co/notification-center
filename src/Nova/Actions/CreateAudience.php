<?php

namespace Opscale\NotificationCenter\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NotificationCenter\Models\Audience;
use Opscale\NotificationCenter\Models\Enums\AudienceType;

class CreateAudience extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Get the displayable name of the action.
     */
    public function name(): string
    {
        return __('Create Audience');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $audience = Audience::create([
            'name' => $fields->get('name'),
            'description' => $fields->get('description'),
            'type' => AudienceType::STATIC,
            'total_members' => $models->count(),
        ]);

        $audience->profiles()->attach($models->pluck('id'));

        return Action::message(__(':count profile(s) added to audience ":name".', [
            'count' => $models->count(),
            'name' => $audience->name,
        ]));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Text::make(__('Name'), 'name')
                ->rules('required', 'string', 'max:255'),

            Textarea::make(__('Description'), 'description')
                ->rules('nullable', 'string', 'max:500'),
        ];
    }
}
