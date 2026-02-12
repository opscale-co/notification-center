<?php

namespace Opscale\NotificationCenter\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ForceDelivery extends Action
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
        return __('Force Delivery');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $messages = config('notification-center.messages', []);

        foreach ($models as $delivery) {
            $channel = $delivery->channel;

            if (! isset($messages[$channel])) {
                return Action::danger(__('No notification class configured for channel: :channel', ['channel' => $channel]));
            }

            $notificationClass = $messages[$channel];
            $profile = $delivery->profile;

            if (! $profile) {
                return Action::danger(__('Profile not found for this delivery.'));
            }

            $profile->notify(new $notificationClass($delivery));
        }

        return Action::message(__('Notification(s) sent successfully!'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
