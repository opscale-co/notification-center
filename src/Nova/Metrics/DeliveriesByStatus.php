<?php

namespace Opscale\NotificationCenter\Nova\Metrics;

use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;
use Opscale\NotificationCenter\Models\Delivery;
use Opscale\NotificationCenter\Models\Enums\DeliveryStatus;

class DeliveriesByStatus extends Partition
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        $latestDeliveries = Delivery::where('notification_id', $request->resourceId)
            ->whereIn('id', function ($query) use ($request) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('notification_center_deliveries')
                    ->where('notification_id', $request->resourceId)
                    ->groupBy('profile_id');
            });

        return $this->count($request, $latestDeliveries, 'status')
            ->label(fn ($value) => __($value))
            ->colors([
                DeliveryStatus::PENDING->value => '#F59E0B',
                DeliveryStatus::FAILED->value => '#EF4444',
                DeliveryStatus::SENT->value => '#3B82F6',
                DeliveryStatus::RECEIVED->value => '#6366F1',
                DeliveryStatus::OPENED->value => '#8B5CF6',
                DeliveryStatus::VERIFIED->value => '#10B981',
                DeliveryStatus::EXPIRED->value => '#6B7280',
            ]);
    }

    /**
     * Get the displayable name of the metric.
     */
    public function name(): string
    {
        return __('Deliveries by status');
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'deliveries-by-status';
    }
}
