<?php

namespace Opscale\NotificationCenter\Nova;

use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NotificationCenter\Models\Blueprint as Model;
use Opscale\NotificationCenter\Nova\Actions\SendBlueprintToProfile;

/**
 * @extends resource<Model>
 */
class Blueprint extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static $model = Model::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array<int, string>
     */
    public static $search = [
        'name',
        'subject',
        'body',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<mixed>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make(__('Name'), 'name')
                ->rules('required', 'string', 'max:255'),

            Text::make(__('Subject'), 'subject')
                ->rules('required', 'string', 'max:255')
                ->help(__('Use {{ variable }} placeholders to insert dynamic values when sending.')),

            Trix::make(__('Body'), 'body')
                ->rules('required', 'string')
                ->help(__('Use {{ variable }} placeholders to insert dynamic values when sending.'))
                ->alwaysShow(),

            Textarea::make(__('Summary'), 'summary')
                ->rules('required', 'string', 'max:255')
                ->alwaysShow(),

            Text::make(__('Action'), 'action')
                ->rules('nullable', 'string', 'max:255')
                ->help(__('Optional action URL. {{ variable }} placeholders are allowed.'))
                ->hideFromIndex(),

            Text::make(__('Detected variables'), fn () => implode(', ', $this->variables()) ?: '—')
                ->exceptOnForms(),
        ];
    }

    /**
     * Get the actions available for the resource.
     */
    public function actions(NovaRequest $request): array
    {
        return [
            SendBlueprintToProfile::make(),
        ];
    }
}
