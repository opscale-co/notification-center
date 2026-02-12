<?php

namespace Opscale\NotificationCenter\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Opscale\NotificationCenter\Models\Enums\DeliveryStatus;
use Opscale\NotificationCenter\Models\Repositories\DeliveryRepository;

class Delivery extends Pivot
{
    use DeliveryRepository, HasUlids, ValidatorTrait;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $table = 'notification_center_deliveries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'profile_id',
        'notification_id',
        'channel',
        'status',
        'open_slug',
        'action_slug',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => DeliveryStatus::class,
    ];

    /**
     * Get the profile that owns the delivery.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the notification that owns the delivery.
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * Get the events for this delivery.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'delivery_id');
    }
}
