<?php

namespace Opscale\NotificationCenter\Nova;

use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Laravel\Nova\Tabs\Tab;
use Opscale\NotificationCenter\Models\Audience as Model;
use Opscale\NotificationCenter\Models\Enums\AudienceType;

/**
 * @extends Resource<Model>
 */
class Audience extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Opscale\NotificationCenter\Models\Audience>
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
     * @var array<string>
     */
    public static $search = [
        'id',
        'name',
        'description',
    ];

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'audiences';
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return __('Audiences');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return __('Audience');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<mixed>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Tab::group(__('Audience'), [
                Tab::make(__('Details'), [
                    Text::make(__('Name'), 'name')
                        ->rules('required', 'string', 'max:255')
                        ->sortable(),

                    Textarea::make(__('Description'), 'description')
                        ->rules('required', 'string')
                        ->alwaysShow(),

                    Select::make(__('Type'), 'type')
                        ->options([
                            AudienceType::STATIC->value => __('Static'),
                            AudienceType::DYNAMIC->value => __('Dynamic'),
                            AudienceType::SEGMENT->value => __('Segment'),
                        ])
                        ->rules('required', 'in:' . implode(',', array_column(AudienceType::cases(), 'value')))
                        ->displayUsingLabels(),

                    Number::make(__('Total Members'), 'total_members')
                        ->exceptOnForms()
                        ->sortable(),

                    DateTime::make(__('Created At'), 'created_at')
                        ->displayUsing(fn ($value) => $value?->diffForHumans())
                        ->exceptOnForms()
                        ->sortable(),

                    DateTime::make(__('Updated At'), 'updated_at')
                        ->displayUsing(fn ($value) => $value?->diffForHumans())
                        ->exceptOnForms(),
                ]),

                Tab::make(__('Profiles'), [
                    BelongsToMany::make(__('Profiles'), 'profiles', Profile::class),
                ]),
            ]),
        ];
    }
}
