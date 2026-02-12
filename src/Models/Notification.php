<?php

namespace Opscale\NotificationCenter\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Opscale\NotificationCenter\Models\Enums\NotificationStatus;
use Opscale\NotificationCenter\Models\Enums\NotificationType;
use Opscale\NovaDynamicResources\Models\Concerns\UsesTemplate;

class Notification extends Model
{
    use HasUlids, SoftDeletes, UsesTemplate, ValidatorTrait;

    protected $table = 'notification_center_notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'subject',
        'body',
        'summary',
        'expiration',
        'action',
        'status',
        'type',
        'template_id',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expiration' => 'datetime',
        'status' => NotificationStatus::class,
        'type' => NotificationType::class,
        'data' => 'array',
    ];

    /**
     * Get the audiences for this notification.
     */
    public function audiences(): BelongsToMany
    {
        return $this->belongsToMany(Audience::class, 'notification_center_audience_notification');
    }

    /**
     * Get the deliveries for this notification.
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}
