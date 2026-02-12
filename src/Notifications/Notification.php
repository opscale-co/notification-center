<?php

namespace Opscale\NotificationCenter\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Queue\SerializesModels;
use Opscale\NotificationCenter\Models\Delivery;

abstract class Notification extends BaseNotification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The delivery record.
     */
    protected Delivery $delivery;

    /**
     * Create a new notification instance.
     */
    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;

        $type = strtolower($delivery->notification->type->value ?? 'transactional');
        $config = config("notification-center.strategies.{$type}");

        $this->onQueue($config['queue'] ?? 'notifications');
        $this->tries = $config['max_attempts'] ?? 2;
        $this->backoff = $config['retry_interval'] ?? [300];
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [$this->delivery->channel];
    }

    /**
     * Get the subscription contact for this notification channel.
     */
    public function getSubscription(): mixed
    {
        return $this->delivery->profile->subscriptions
            ->where('type', $this->delivery->channel)
            ->first()
            ?->contact;
    }

    /**
     * Get the delivery record.
     */
    public function getDelivery(): Delivery
    {
        return $this->delivery;
    }
}
