<?php

namespace Opscale\NotificationCenter\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use Opscale\NotificationCenter\Models\Enums\AudienceType;
use Opscale\NotificationCenter\Models\Repositories\AudienceRepository;
use Opscale\Validations\Validatable;

class Audience extends Model
{
    use AudienceRepository, HasUlids, SoftDeletes, Validatable;

    protected $table = 'notification_center_audiences';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'type',
        'criteria',
        'total_members',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => AudienceType::class,
        'criteria' => 'array',
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', Rule::enum(AudienceType::class)],
            'criteria' => ['nullable', 'json'],
            'total_members' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get the notifications for this audience.
     */
    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, 'notification_center_audience_notification');
    }

    /**
     * Get the profiles that belong to this audience.
     */
    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'notification_center_audience_profile');
    }
}
