<?php

namespace Opscale\NotificationCenter\Models\Repositories;

use Illuminate\Support\Str;

trait DeliveryRepository
{
    public static function bootDeliveryRepository(): void
    {
        static::creating(function ($delivery) {
            $delivery->open_slug = Str::random(5);

            if ($delivery->notification?->action) {
                $delivery->action_slug = Str::random(5);
            }
        });
    }
}
