<?php

namespace Opscale\NotificationCenter\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Opscale\Validations\Validatable;

class Event extends Model
{
    use HasUlids, Validatable;

    protected $table = 'notification_center_events';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'delivery_id',
        'name',
        'payload',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payload' => 'array',
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
            'delivery_id' => ['required', 'ulid'],
            'name' => ['required', 'string', 'max:255'],
            'payload' => ['nullable', 'json'],
        ];
    }

    /**
     * Get the delivery that owns the event.
     */
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }
}
