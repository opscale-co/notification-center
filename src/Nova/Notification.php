<?php

namespace Opscale\NotificationCenter\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Laravel\Nova\Resource;
use Laravel\Nova\Tabs\Tab;
use Opscale\NotificationCenter\Models\Enums\NotificationStatus;
use Opscale\NotificationCenter\Models\Enums\NotificationType;
use Opscale\NotificationCenter\Models\Notification as Model;
use Opscale\NotificationCenter\Nova\Actions\PublishNotification;
use Opscale\NotificationCenter\Nova\Metrics\DeliveriesByStatus;
use Opscale\NotificationCenter\Nova\Metrics\DeliveriesPerDay;
use Opscale\NotificationCenter\Nova\Metrics\TotalTargets;
use Opscale\NovaDynamicResources\Nova\Concerns\UsesTemplate;

/**
 * @extends Resource<Model>
 */
class Notification extends Resource
{
    use UsesTemplate;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Opscale\NotificationCenter\Models\Notification>
     */
    public static $model = Model::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'subject';

    /**
     * The columns that should be searched.
     *
     * @var array<int, string>
     */
    public static $search = [
        'subject',
        'body',
        'summary',
    ];

    /**
     * Determine if the current user can update the given resource.
     */
    public function authorizedToUpdate(Request $request): bool
    {
        return $this->resource->status !== NotificationStatus::PUBLISHED
            && parent::authorizedToUpdate($request);
    }

    /**
     * Determine if the current user can delete the given resource.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return $this->resource->status !== NotificationStatus::PUBLISHED
            && parent::authorizedToDelete($request);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<mixed>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Tab::group(__('Notification'), [
                Tab::make(__('Details'), [
                    ...$this->fieldsForCreate(),

                    Badge::make(__('Status'), 'status')
                        ->map([
                            NotificationStatus::DRAFT->value => 'warning',
                            NotificationStatus::PUBLISHED->value => 'success',
                        ]),
                ]),

                Tab::make(__('Deliveries'), [
                    HasMany::make(__('Deliveries'), 'deliveries', Delivery::class),
                ]),
            ]),
        ];
    }

    /**
     * Get the fields used for creating and updating the resource.
     *
     * @return array<mixed>
     */
    public function fieldsForCreate(): array
    {
        return [
            Text::make(__('Subject'), 'subject')
                ->rules('required', 'string', 'max:50'),

            Trix::make(__('Body'), 'body')
                ->rules('required', 'string')
                ->alwaysShow(),

            Textarea::make(__('Summary'), 'summary')
                ->rules('required', 'string', 'max:100')
                ->alwaysShow(),

            ...$this->renderTemplateFields(),

            Panel::make(__('Advanced Settings'), [
                DateTime::make(__('Expiration'), 'expiration')
                    ->displayUsing(fn ($value) => $value?->diffForHumans())
                    ->rules('nullable', 'date'),

                Text::make(__('Action'), 'action')
                    ->rules('nullable', 'url', 'max:255')
                    ->hideFromIndex(),

                Select::make(__('Type'), 'type')
                    ->options([
                        NotificationType::MARKETING->value => __('Marketing'),
                        NotificationType::TRANSACTIONAL->value => __('Transactional'),
                        NotificationType::SYSTEM->value => __('System'),
                        NotificationType::ALERT->value => __('Alert'),
                        NotificationType::REMINDER->value => __('Reminder'),
                    ])
                    ->default(NotificationType::TRANSACTIONAL->value)
                    ->rules('required', 'in:' . implode(',', array_column(NotificationType::cases(), 'value')))
                    ->hideFromIndex(),
            ])->collapsible()->collapsedByDefault(),
        ];
    }

    /**
     * Get the cards available for the resource.
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [
            TotalTargets::make()->onlyOnDetail(),
            DeliveriesPerDay::make()->onlyOnDetail(),
            DeliveriesByStatus::make()->onlyOnDetail(),
        ];
    }

    /**
     * Get the actions available for the resource.
     */
    public function actions(NovaRequest $request): array
    {
        return [
            PublishNotification::make(),
        ];
    }
}
