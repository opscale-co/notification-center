<?php

namespace Opscale\NotificationCenter\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Opscale\NotificationCenter\Models\Repositories\BlueprintRepository;
use Opscale\Validations\Validatable;

class Blueprint extends Model
{
    use BlueprintRepository, HasUlids, SoftDeletes, Validatable;

    protected $table = 'notification_center_blueprints';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'subject',
        'body',
        'summary',
        'action',
    ];

    protected static function booted(): void
    {
        static::validateOnSaving();
    }

    /**
     * The validation rules for this model.
     *
     * Note: length limits are looser than Notification's because the stored
     * content carries {{ variable }} placeholders; the final substituted values
     * are validated by Notification when the blueprint is sent.
     *
     * @return array<string, mixed>
     */
    public function validationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'summary' => ['required', 'string', 'max:255'],
            'action' => ['nullable', 'string', 'max:255'],
        ];
    }
}
