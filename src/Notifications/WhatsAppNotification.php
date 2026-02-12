<?php

namespace Opscale\NotificationCenter\Notifications;

use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioContentTemplateMessage;

class WhatsAppNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [TwilioChannel::class];
    }

    /**
     * Get the Twilio WhatsApp representation of the notification.
     */
    public function toTwilio(object $notifiable): TwilioContentTemplateMessage
    {
        $model = $this->delivery->notification;
        $data = [
            1 => $model->subject,
            2 => $model->summary,
            3 => route('notification-center.track.open', $this->delivery->open_slug),
        ];

        return (new TwilioContentTemplateMessage)
            ->contentSid(config('notification-center.whatsapp_content_sid'))
            ->contentVariables($data);
    }
}
