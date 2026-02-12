<?php

namespace Opscale\NotificationCenter\Notifications;

use Illuminate\Notifications\Slack\BlockKit\Blocks\ActionsBlock;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackChannel;
use Illuminate\Notifications\Slack\SlackMessage;

class SlackNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [SlackChannel::class];
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(object $notifiable): SlackMessage
    {
        $model = $this->delivery->notification;

        $message = (new SlackMessage)
            ->text($model->subject)
            ->headerBlock($model->subject)
            ->sectionBlock(function (SectionBlock $block) use ($model) {
                $block->text($model->summary ?? $model->subject);
            });

        $url = route('notification-center.track.open', $this->delivery->open_slug);

        $message->actionsBlock(function (ActionsBlock $block) use ($url) {
            $block->button(__('View'))->url($url);
        });

        return $message;
    }
}
