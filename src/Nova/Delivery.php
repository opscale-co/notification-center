<?php

namespace Opscale\NotificationCenter\Nova;

use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Laravel\Nova\Tabs\Tab;
use Opscale\NotificationCenter\Models\Delivery as DeliveryModel;
use Opscale\NotificationCenter\Nova\Actions\ForceDelivery;

/**
 * @extends Resource<DeliveryModel>
 */
class Delivery extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Opscale\NotificationCenter\Models\Delivery>
     */
    public static $model = DeliveryModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'id', 'channel',
    ];

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'deliveries';
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return __('Deliveries');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return __('Delivery');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<mixed>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Tab::group(__('Delivery'), [
                Tab::make(__('Details'), [
                    BelongsTo::make(__('Profile'), 'profile', Profile::class)
                        ->searchable()
                        ->required(),

                    BelongsTo::make(__('Notification'), 'notification', Notification::class)
                        ->searchable()
                        ->required(),

                    Text::make(__('Channel'), 'channel')
                        ->rules('required', 'string', 'max:255')
                        ->sortable(),

                    Badge::make(__('Status'), 'status')
                        ->map([
                            'Pending' => 'warning',
                            'Failed' => 'danger',
                            'Sent' => 'info',
                            'Received' => 'info',
                            'Opened' => 'info',
                            'Verified' => 'success',
                            'Expired' => 'danger',
                        ])
                        ->sortable(),

                    DateTime::make(__('Created At'), 'created_at')
                        ->displayUsing(fn ($value) => $value?->diffForHumans())
                        ->exceptOnForms()
                        ->sortable(),

                    DateTime::make(__('Updated At'), 'updated_at')
                        ->displayUsing(fn ($value) => $value?->diffForHumans())
                        ->exceptOnForms(),
                ]),

                Tab::make(__('Events'), [
                    HasMany::make(__('Events'), 'events', Event::class),
                ]),
            ]),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [
            ForceDelivery::make(),
        ];
    }
}
