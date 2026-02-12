<?php

namespace Opscale\NotificationCenter\Nova\Dashboards;

use Laravel\Nova\Dashboard;
use Opscale\NotificationCenter\Models\Delivery;
use Opscale\NotificationCenter\Models\Enums\DeliveryStatus;
use Opscale\NotificationCenter\Models\Profile;
use Opscale\NotificationCenter\Notifications\CardNotification;
use Opscale\NotificationCenter\Nova\Cards\NotificationCard;

class NotificationsDashboard extends Dashboard
{
    /**
     * Get the displayable name of the dashboard.
     */
    public function name(): string
    {
        return __('Notifications');
    }

    /**
     * Get the cards that should be displayed on the Nova dashboard.
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $profile = Profile::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->getKey())
            ->first();

        if (! $profile) {
            return [];
        }

        $cards = [];

        if ($profile->pushSubscriptions()->doesntExist()) {
            $cards[] = NotificationCard::make()
                ->title(__('Enable Push Notifications'))
                ->subtitle(__('Stay updated with real-time alerts and messages by enabling push notifications.'))
                ->variant('warning')
                ->actionLabel(__('Subscribe'))
                ->actionUrl(route('notification-center.webpush.subscribe', $profile->id))
                ->actionTarget('_blank');
        }

        $deliveries = Delivery::with('notification')
            ->where('profile_id', $profile->id)
            ->whereIn('status', [
                DeliveryStatus::SENT->value,
                DeliveryStatus::RECEIVED->value,
            ])
            ->latest()
            ->get();

        foreach ($deliveries as $delivery) {
            $cards[] = (new CardNotification($delivery))->toCard($delivery->profile);
        }

        return $cards;
    }
}
