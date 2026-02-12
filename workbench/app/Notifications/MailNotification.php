<?php

namespace Workbench\App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Opscale\NotificationCenter\Notifications\DynamicNotification;

class MailNotification extends DynamicNotification
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->notification->data['subject'] ?? 'Notification')
            ->line($this->notification->data['summary'] ?? '')
            ->when(
                isset($this->notification->data['action_url']) && isset($this->notification->data['action_text']),
                fn (MailMessage $mail) => $mail->action(
                    $this->notification->data['action_text'],
                    $this->notification->data['action_url']
                )
            );
    }

    /**
     * Process the notification for the specific channel.
     *
     * @param  mixed  $notifiable
     * @return mixed
     */
    protected function process($notifiable)
    {
        return $this->toMail($notifiable);
    }
}
