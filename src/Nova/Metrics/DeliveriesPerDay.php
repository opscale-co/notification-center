<?php

namespace Opscale\NotificationCenter\Nova\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;
use Opscale\NotificationCenter\Models\Delivery;

class DeliveriesPerDay extends Trend
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): TrendResult
    {
        return $this->countByDays(
            $request,
            Delivery::where('notification_id', $request->resourceId),
        );
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int, string>
     */
    public function ranges(): array
    {
        return [
            7 => __('7 Days'),
            14 => __('14 Days'),
            30 => __('30 Days'),
            60 => __('60 Days'),
            90 => __('90 Days'),
        ];
    }

    /**
     * Get the displayable name of the metric.
     */
    public function name(): string
    {
        return __('Deliveries per day');
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'deliveries-per-day';
    }
}
