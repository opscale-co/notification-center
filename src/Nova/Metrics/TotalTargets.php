<?php

namespace Opscale\NotificationCenter\Nova\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;
use Opscale\NotificationCenter\Models\Delivery;

class TotalTargets extends Value
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): ValueResult
    {
        return $this->count(
            $request,
            Delivery::where('notification_id', $request->resourceId),
            'profile_id',
        )->allowZeroResult();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<string, string>
     */
    public function ranges(): array
    {
        return [];
    }

    /**
     * Get the displayable name of the metric.
     */
    public function name(): string
    {
        return __('Total targets');
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'total-targets';
    }
}
