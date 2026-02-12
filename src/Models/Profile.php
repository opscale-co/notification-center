<?php

namespace Opscale\NotificationCenter\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

class Profile extends Model
{
    use HasPushSubscriptions, HasUlids, Notifiable, SoftDeletes, ValidatorTrait;

    protected $table = 'notification_center_profiles';

    /**
     * The relationships that should always be loaded.
     *
     * @var array<string>
     */
    protected $with = ['notifiable', 'subscriptions'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
    ];

    /**
     * Get the notifiable entity.
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the subscriptions for this profile.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the deliveries for this profile.
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    /**
     * Get the audiences this profile belongs to.
     */
    public function audiences(): BelongsToMany
    {
        return $this->belongsToMany(Audience::class, 'notification_center_audience_profile');
    }

    /**
     * Route the notification for the given driver.
     */
    public function routeNotificationFor($driver, $notification = null): mixed
    {
        if ($notification && method_exists($notification, 'getSubscription')) {
            return $notification->getSubscription();
        }

        return parent::routeNotificationFor($driver, $notification);
    }
}
