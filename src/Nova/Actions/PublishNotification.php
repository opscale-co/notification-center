<?php

namespace Opscale\NotificationCenter\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NotificationCenter\Jobs\ExecuteNotificationStrategy;
use Opscale\NotificationCenter\Models\Audience;
use Opscale\NotificationCenter\Models\Enums\NotificationStatus;

class PublishNotification extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * Get the displayable name of the action.
     */
    public function name(): string
    {
        return __('Publish Notification');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $audienceId = $fields->get('audience_id');

        $audience = Audience::find($audienceId);

        if (! $audience) {
            return Action::danger(__('Invalid audience selected.'));
        }

        foreach ($models as $notification) {
            $notification->update([
                'status' => NotificationStatus::PUBLISHED,
            ]);

            $notification->audiences()->attach($audience->id);

            ExecuteNotificationStrategy::dispatch($notification);
        }

        return Action::message(__('Notification(s) published successfully!'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Select::make(__('Audience'), 'audience_id')
                ->options(Audience::pluck('name', 'id'))
                ->rules('required')
                ->help(__('Select the audience to send the notification to.')),
        ];
    }
}
