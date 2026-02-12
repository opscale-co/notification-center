<?php

namespace Opscale\NotificationCenter\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Opscale\NotificationCenter\Models\Delivery;

class SendDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Delivery $delivery,
    ) {
        $type = strtolower($this->delivery->notification->type->value ?? 'transactional');
        $queue = config("notification-center.strategies.{$type}.queue", 'notifications');

        $this->onQueue($queue);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $channel = $this->delivery->channel;
        $messages = config('notification-center.messages', []);

        if (! isset($messages[$channel])) {
            return;
        }

        $notificationClass = $messages[$channel];
        $profile = $this->delivery->profile;

        if (! $profile) {
            return;
        }

        $profile->notify(new $notificationClass($this->delivery));
    }
}
