<?php

namespace Opscale\NotificationCenter\Notifications;

use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WebPushNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush(object $notifiable, mixed $notification): WebPushMessage
    {
        $model = $this->delivery->notification;

        $message = (new WebPushMessage)
            ->title($model->subject)
            ->body($model->summary)
            ->icon(asset('favicon.png'));

        $message->action(route('notification-center.track.open', $this->delivery->open_slug));

        return $message;
    }

    public function getSubscription(): mixed
    {
        return $this->delivery->profile->pushSubscriptions;
    }
}
