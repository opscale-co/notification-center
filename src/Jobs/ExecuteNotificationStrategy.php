<?php

namespace Opscale\NotificationCenter\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Opscale\NotificationCenter\Models\Delivery;
use Opscale\NotificationCenter\Models\Enums\DeliveryStatus;
use Opscale\NotificationCenter\Models\Notification;

class ExecuteNotificationStrategy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $strategy;

    protected array $channels;

    protected int $timeoutHours;

    protected array $messages;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Notification $notification,
    ) {
        $type = strtolower($this->notification->type->value);
        $queue = config("notification-center.strategies.{$type}.queue", 'notifications');

        $this->onQueue($queue);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $type = strtolower($this->notification->type->value);
        $this->strategy = config("notification-center.strategies.{$type}");
        $this->channels = $this->strategy['channels'] ?? [];
        $this->timeoutHours = $this->strategy['timeout_per_channel'] ?? 24;
        $this->messages = config('notification-center.messages', []);

        if (empty($this->channels)) {
            return;
        }

        foreach ($this->notification->audiences as $audience) {
            $profiles = $audience->profiles()->with(['deliveries' => function ($query) {
                $query->where('notification_id', $this->notification->id)->orderBy('created_at');
            }])->get();

            foreach ($profiles as $profile) {
                $this->processProfile($profile);
            }
        }
    }

    /**
     * Process a single profile for delivery.
     */
    protected function processProfile($profile): void
    {
        $profileDeliveries = $profile->deliveries;

        $firstChannel = $this->channels[0];

        if ($profileDeliveries->isEmpty()) {
            $delivery = Delivery::create([
                'profile_id' => $profile->id,
                'notification_id' => $this->notification->id,
                'channel' => $firstChannel,
                'status' => DeliveryStatus::PENDING,
            ]);

            $this->sendDelivery($delivery);

            return;
        }

        $latestDelivery = $profileDeliveries->last();

        if (in_array($latestDelivery->status, [DeliveryStatus::OPENED, DeliveryStatus::VERIFIED])) {
            return;
        }

        $nextChannel = $this->resolveNextChannel($latestDelivery);

        if (! $nextChannel) {
            return;
        }

        $delivery = Delivery::create([
            'profile_id' => $profile->id,
            'notification_id' => $this->notification->id,
            'channel' => $nextChannel,
            'status' => DeliveryStatus::PENDING,
        ]);

        $this->sendDelivery($delivery);
    }

    /**
     * Resolve the next channel for a delivery based on timeout and channel escalation.
     */
    protected function resolveNextChannel(Delivery $latestDelivery): ?string
    {
        $currentChannelIndex = array_search($latestDelivery->channel, $this->channels);

        if ($currentChannelIndex === false) {
            return null;
        }

        $nextChannelIndex = $currentChannelIndex + 1;

        if ($nextChannelIndex >= count($this->channels)) {
            return null;
        }

        $elapsedTime = $this->calculateAvailability($latestDelivery->created_at, now());

        if ($elapsedTime < $this->timeoutHours) {
            return null;
        }

        return $this->channels[$nextChannelIndex];
    }

    /**
     * Calculate the number of hours elapsed within allowed timeslots between two dates.
     */
    protected function calculateAvailability(Carbon $from, Carbon $to): float
    {
        $days = $this->strategy['days'] ?? [0, 1, 2, 3, 4, 5, 6];
        $hours = $this->strategy['hours'] ?? ['00:00', '23:59'];

        $slotStart = $hours[0];
        $slotEnd = $hours[1];

        $totalMinutes = 0;
        $cursor = $from->copy();

        while ($cursor->lt($to)) {
            if (! in_array($cursor->dayOfWeek, $days)) {
                $cursor->addDay()->setTimeFromTimeString($slotStart);

                continue;
            }

            $dayStart = $cursor->copy()->setTimeFromTimeString($slotStart);
            $dayEnd = $cursor->copy()->setTimeFromTimeString($slotEnd);

            if ($cursor->lt($dayStart)) {
                $cursor = $dayStart;
            }

            if ($cursor->gte($dayEnd)) {
                $cursor->addDay()->setTimeFromTimeString($slotStart);

                continue;
            }

            $end = $to->lt($dayEnd) ? $to : $dayEnd;
            $totalMinutes += $cursor->diffInMinutes($end);
            $cursor = $dayEnd->copy()->addDay()->setTimeFromTimeString($slotStart);
        }

        return $totalMinutes / 60;
    }

    /**
     * Dispatch the notification for a delivery.
     */
    protected function sendDelivery(Delivery $delivery): void
    {
        $channel = $delivery->channel;

        if (! isset($this->messages[$channel])) {
            return;
        }

        $notificationClass = $this->messages[$channel];
        $notification = new $notificationClass($delivery);
        $recipient = $notification->getSubscription();

        if (! $recipient) {
            return;
        }

        if ($recipient instanceof Model) {
            $recipient->notify($notification);
        } else {
            $delivery->profile->notify($notification);
        }
    }
}
