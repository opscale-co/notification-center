<?php

namespace Opscale\NotificationCenter\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NotificationCenter\Jobs\ExecuteNotificationStrategy;
use Opscale\NotificationCenter\Models\Blueprint;
use Opscale\NotificationCenter\Models\Enums\NotificationStatus;
use Opscale\NotificationCenter\Models\Notification;
use Opscale\NotificationCenter\Models\Profile;

class SendBlueprintToProfile extends Action
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

        $values = $fields->get('variables') ?? [];

        // Nova submits the KeyValue as a JSON string; decode defensively.
        if (is_string($values)) {
            $values = json_decode($values, true) ?: [];
        }

        foreach ($models as $blueprint) {
            try {
                $notification = Notification::create($blueprint->toNotificationAttributes($values) + [
                    'status' => NotificationStatus::PUBLISHED,
                    'blueprint_id' => $blueprint->id,
                ]);
            } catch (ValidationException $exception) {
                return Action::danger(__('The resulting notification is invalid: :message', [
                    'message' => $exception->getMessage(),
                ]));
            }

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

            KeyValue::make(__('Variables'), 'variables')
                ->keyLabel(__('Variable'))
                ->valueLabel(__('Value'))
                ->default($this->variablesPrefill($request))
                ->rules('nullable', 'json')
                ->help(__('Fill in a value for each dynamic variable detected in the blueprint.')),
        ];
    }

    /**
     * Build the prefilled variables map ({name => ''}) from the selected blueprint.
     *
     * @return array<string, string>
     */
    protected function variablesPrefill(NovaRequest $request): array
    {
        $blueprint = Blueprint::find($request->resourceId);

        if (! $blueprint) {
            return [];
        }

        return array_fill_keys($blueprint->variables(), '');
    }
}
