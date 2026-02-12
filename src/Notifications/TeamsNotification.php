<?php

namespace Opscale\NotificationCenter\Notifications;

use NotificationChannels\MicrosoftTeams\Actions\ActionOpenUrl;
use NotificationChannels\MicrosoftTeams\ContentBlocks\TextBlock;
use NotificationChannels\MicrosoftTeams\MicrosoftTeamsAdaptiveCard;
use NotificationChannels\MicrosoftTeams\MicrosoftTeamsChannel;

class TeamsNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [MicrosoftTeamsChannel::class];
    }

    /**
     * Get the Microsoft Teams representation of the notification.
     */
    public function toMicrosoftTeams(object $notifiable): MicrosoftTeamsAdaptiveCard
    {
        $model = $this->delivery->notification;

        $card = MicrosoftTeamsAdaptiveCard::create()
            ->title($model->subject)
            ->content([
                TextBlock::create()
                    ->setText($model->summary ?? $model->subject)
                    ->setIsSubtle(true),
            ]);

        $url = route('notification-center.track.open', $this->delivery->open_slug);

        $card->actions([
            ActionOpenUrl::create()
                ->setTitle(__('View'))
                ->setUrl($url),
        ]);

        return $card;
    }
}
