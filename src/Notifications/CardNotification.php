<?php

namespace Opscale\NotificationCenter\Notifications;

use Opscale\NotificationCenter\Nova\Cards\NotificationCard;

class CardNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['card'];
    }

    /**
     * Get the card representation of the notification.
     */
    public function toCard(object $notifiable): NotificationCard
    {
        $model = $this->delivery->notification;

        $card = NotificationCard::make()
            ->title($model->subject)
            ->subtitle($model->summary)
            ->variant($this->getVariant($model->type->value));

        $card->actionLabel(__('View'))
            ->actionUrl(route('notification-center.track.open', $this->delivery->open_slug))
            ->actionTarget('_blank');

        return $card;
    }

    public function getSubscription(): mixed
    {
        $contact = parent::getSubscription();

        return $this->delivery->profile->notifiable::find($contact);
    }

    /**
     * Get the card variant based on notification type.
     */
    protected function getVariant(string $type): string
    {
        return match ($type) {
            'Alert' => 'danger',
            'System' => 'warning',
            default => 'primary',
        };
    }
}
