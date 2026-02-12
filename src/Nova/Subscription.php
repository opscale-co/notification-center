<?php

namespace Opscale\NotificationCenter\Nova;

use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NotificationCenter\Models\Subscription as Model;

/**
 * @extends Resource<Model>
 */
class Subscription extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Opscale\NotificationCenter\Models\Subscription>
     */
    public static $model = Model::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'contact';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'id',
        'type',
        'contact',
    ];

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'subscriptions';
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return __('Subscriptions');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return __('Subscription');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<mixed>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Select::make(__('Type'), 'type')
                ->options($this->getChannelTypes())
                ->rules('required', 'string')
                ->displayUsingLabels()
                ->sortable(),

            Text::make(__('Contact'), 'contact')
                ->rules('required', 'string', 'max:255')
                ->help(__('Email address, phone number, or webhook URL'))
                ->sortable(),

            Boolean::make(__('Verified'), 'verified')
                ->default(false),

            Number::make(__('Priority'), 'priority')
                ->min(1)
                ->max(10)
                ->default(5)
                ->rules('required', 'integer', 'min:1', 'max:10')
                ->help(__('Priority from 1 (highest) to 10 (lowest)'))
                ->sortable(),

            DateTime::make(__('Created At'), 'created_at')
                ->displayUsing(fn ($value) => $value?->diffForHumans())
                ->exceptOnForms()
                ->sortable(),

            DateTime::make(__('Updated At'), 'updated_at')
                ->displayUsing(fn ($value) => $value?->diffForHumans())
                ->exceptOnForms(),
        ];
    }

    /**
     * Get available channel types from config.
     *
     * @return array<string, string>
     */
    protected function getChannelTypes(): array
    {
        $messages = config('notification-center.messages', []);

        return collect(array_keys($messages))
            ->mapWithKeys(fn ($key) => [$key => __(ucfirst($key))])
            ->all();
    }
}
