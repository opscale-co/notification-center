<?php

namespace Opscale\NotificationCenter\Nova\Repeatables;

use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NotificationCenter\Models\Subscription as Model;

class Subscription extends Repeatable
{
    /**
     * The underlying model the repeatable represents.
     *
     * @var class-string<Model>
     */
    public static $model = Model::class;

    /**
     * Get the fields displayed by the repeatable.
     *
     * @return array<mixed>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Select::make(__('Type'), 'type')
                ->options($this->getChannelTypes())
                ->displayUsingLabels()
                ->rules('required', 'string'),

            Text::make(__('Contact'), 'contact')
                ->rules('required', 'string', 'max:255')
                ->help(__('Email address, phone number, or webhook URL')),

            Boolean::make(__('Verified'), 'verified')
                ->default(false),

            Number::make(__('Priority'), 'priority')
                ->min(1)
                ->max(10)
                ->default(5)
                ->rules('required', 'integer', 'min:1', 'max:10')
                ->help(__('Priority from 1 (highest) to 10 (lowest)')),
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
