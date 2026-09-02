<?php

namespace Opscale\NotificationCenter\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Opscale\Validations\Validatable;

class Subscription extends Model
{
    use HasUlids, SoftDeletes, Validatable;

    protected $table = 'notification_center_subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'profile_id',
        'type',
        'contact',
        'verified',
        'priority',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified' => 'boolean',
        'priority' => 'integer',
    ];

    protected static function booted(): void
    {
        static::validateOnSaving();
    }

    /**
     * The validation rules for this model.
     *
     * @return array<string, mixed>
     */
    public function validationRules(): array
    {
        return [
            'profile_id' => ['required', 'ulid'],
            'type' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:255'],
            'verified' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:255'],
        ];
    }

    /**
     * Get the profile this subscription belongs to.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
