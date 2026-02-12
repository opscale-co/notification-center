<?php

namespace Opscale\NotificationCenter\Notifications;

use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Messages\MailMessage;

class EmailNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [MailChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $model = $this->delivery->notification;

        $message = (new MailMessage)
            ->subject($model->subject)
            ->greeting(__('Hello') . ' ' . $notifiable->name)
            ->line($model->summary ?? $model->body);

        if ($this->delivery->open_slug) {
            $message->action(__('View'), route('notification-center.track.open', $this->delivery->open_slug));
        }

        return $message;
    }
}
