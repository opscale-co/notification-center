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
use Opscale\NotificationCenter\Models\Enums\NotificationStatus;
use Opscale\NotificationCenter\Models\Profile;

class SendToProfile extends Action
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
        return __('Send to Profile');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $profile = Profile::find($fields->get('profile_id'));

        if (! $profile) {
            return Action::danger(__('Invalid profile selected.'));
        }

        foreach ($models as $notification) {
            $notification->update([
                'status' => NotificationStatus::PUBLISHED,
            ]);

            ExecuteNotificationStrategy::dispatch($notification, $profile);
        }

        return Action::message(__('Notification(s) sent to profile successfully!'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Select::make(__('Profile'), 'profile_id')
                ->searchable()
                ->options(
                    Profile::with('notifiable')->get()
                        ->mapWithKeys(fn (Profile $profile) => [
                            $profile->id => $profile->notifiable?->name ?? $profile->id,
                        ])
                )
                ->rules('required')
                ->help(__('Select the profile to send the notification to.')),
        ];
    }
}
