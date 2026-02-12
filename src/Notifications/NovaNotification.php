<?php

namespace Opscale\NotificationCenter\Notifications;

use Laravel\Nova\Notifications\NovaChannel;
use Laravel\Nova\Notifications\NovaNotification as NovaMessage;
use Laravel\Nova\URL;

class NovaNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [NovaChannel::class];
    }

    /**
     * Get the Nova representation of the notification.
     */
    public function toNova(object $notifiable): NovaMessage
    {
        $model = $this->delivery->notification;
        $type = $model->type->value;

        $message = NovaMessage::make()
            ->message($model->subject)
            ->icon($this->getIcon($type))
            ->type($this->getNovaType($type));

        $url = route('notification-center.track.open', $this->delivery->open_slug);
        $message->action(__('View'), URL::remote($url))->openInNewTab();

        return $message;
    }

    public function getSubscription(): mixed
    {
        $contact = parent::getSubscription();

        return $this->delivery->profile->notifiable::find($contact);
    }

    /**
     * Get the icon based on notification type.
     */
    protected function getIcon(string $type): string
    {
        return match ($type) {
            'Alert' => 'exclamation-circle',
            'System' => 'cog',
            'Reminder' => 'clock',
            'Marketing' => 'megaphone',
            default => 'bell',
        };
    }

    /**
     * Get the Nova notification type based on notification type.
     */
    protected function getNovaType(string $type): string
    {
        return match ($type) {
            'Alert' => 'error',
            'System' => 'warning',
            default => 'info',
        };
    }
}
