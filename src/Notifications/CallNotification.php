<?php

namespace Opscale\NotificationCenter\Notifications;

use NotificationChannels\Twilio\TwilioCallMessage;
use NotificationChannels\Twilio\TwilioChannel;

class CallNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [TwilioChannel::class];
    }

    /**
     * Get the Twilio call representation of the notification.
     */
    public function toTwilio(object $notifiable): TwilioCallMessage
    {
        $model = $this->delivery->notification;

        return (new TwilioCallMessage)
            ->url(route('notification-center.twiml.call-notification', $this->delivery->id));
    }
}
