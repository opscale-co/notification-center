<?php

namespace Opscale\NotificationCenter\Notifications;

use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class SmsNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [TwilioChannel::class];
    }

    /**
     * Get the Twilio SMS representation of the notification.
     */
    public function toTwilio(object $notifiable): TwilioSmsMessage
    {
        $model = $this->delivery->notification;

        $url = route('notification-center.track.open', $this->delivery->open_slug);

        return (new TwilioSmsMessage)
            ->content($model->summary . "\n\n" . $url);
    }
}
