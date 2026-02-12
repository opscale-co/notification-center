<?php

namespace Opscale\NotificationCenter\Nova;

use Illuminate\Notifications\Notifiable;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Laravel\Nova\Tabs\Tab;
use Opscale\NotificationCenter\Models\Profile as Model;
use Opscale\NotificationCenter\Nova\Actions\CreateAudience;

/**
 * @extends Resource<Model>
 */
class Profile extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Opscale\NotificationCenter\Models\Profile>
     */
    public static $model = Model::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'notifiable.name';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'profiles';
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return __('Profiles');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return __('Profile');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<mixed>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Tab::group(__('Profile'), [
                Tab::make(__('Details'), [
                    MorphTo::make(__('Notifiable'), 'notifiable')
                        ->types($this->getNotifiableResourceTypes())
                        ->required()
                        ->sortable()
                        ->filterable(),

                    DateTime::make(__('Created At'), 'created_at')
                        ->displayUsing(fn ($value) => $value?->diffForHumans())
                        ->exceptOnForms()
                        ->sortable(),

                    DateTime::make(__('Updated At'), 'updated_at')
                        ->displayUsing(fn ($value) => $value?->diffForHumans())
                        ->exceptOnForms(),
                ]),

                Tab::make(__('Subscriptions'), [
                    HasMany::make(__('Subscriptions'), 'subscriptions', Subscription::class),
                ]),
            ]),
        ];
    }

    /**
     * Get the actions available for the resource.
     */
    public function actions(NovaRequest $request): array
    {
        return [
            CreateAudience::make(),
        ];
    }

    /**
     * Get notifiable resource types.
     *
     * @return array<class-string<resource>>
     */
    protected function getNotifiableResourceTypes(): array
    {
        return collect(Nova::$resources)
            ->filter(function ($resource) {
                $model = $resource::$model ?? null;

                if (! $model || ! class_exists($model)) {
                    return false;
                }

                $traits = class_uses_recursive($model);

                return in_array(Notifiable::class, $traits);
            })
            ->values()
            ->all();
    }
}
