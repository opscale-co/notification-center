<?php

namespace Opscale\NotificationCenter\Tests;

use Illuminate\Database\Schema\Blueprint as SchemaBlueprint;
use Illuminate\Support\Facades\Schema;
use Opscale\NotificationCenter\Models\Audience;
use Opscale\NotificationCenter\Models\Blueprint;
use Opscale\NotificationCenter\Models\Delivery;
use Opscale\NotificationCenter\Models\Enums\AudienceType;
use Opscale\NotificationCenter\Models\Enums\DeliveryStatus;
use Opscale\NotificationCenter\Models\Enums\NotificationType;
use Opscale\NotificationCenter\Models\Notification;
use Opscale\NotificationCenter\Models\Profile;
use Workbench\App\Models\User;

/**
 * Shared harness for the Opscale Action Feature tests. Mirrors SendToProfileTest:
 * it stands up the polymorphic `users` table (the profile's notifiable) on the
 * shared in-memory connection and exposes factory helpers for the domain models.
 */
abstract class ActionTestCase extends TestCase
{
    /**
     * Create the polymorphic `users` table (the profile's notifiable) in-process
     * on the shared test connection. loadLaravelMigrations() cannot be used here:
     * it migrates in a subprocess, which gets its own :memory: database.
     */
    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (SchemaBlueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function makeNotification(array $attributes = []): Notification
    {
        return Notification::create(array_merge([
            'subject' => 'Hello',
            'body' => 'Some body',
            'summary' => 'Some summary',
            'type' => NotificationType::TRANSACTIONAL,
        ], $attributes));
    }

    protected function makeProfile(): Profile
    {
        $user = User::factory()->create();

        return Profile::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
        ]);
    }

    protected function makeBlueprint(array $attributes = []): Blueprint
    {
        return Blueprint::create(array_merge([
            'name' => 'Welcome',
            'subject' => 'Hi {{ name }}',
            'body' => 'Welcome {{ name }} to the platform',
            'summary' => 'Welcome {{ name }}',
        ], $attributes));
    }

    protected function makeDelivery(array $attributes = []): Delivery
    {
        $notification = $attributes['notification'] ?? $this->makeNotification();
        $profile = $attributes['profile'] ?? $this->makeProfile();

        return Delivery::create([
            'profile_id' => $profile->id,
            'notification_id' => $notification->id,
            'channel' => $attributes['channel'] ?? 'nova',
            'status' => $attributes['status'] ?? DeliveryStatus::PENDING,
        ]);
    }

    protected function makeAudience(): Audience
    {
        return Audience::create([
            'name' => 'Default',
            'description' => 'Default audience',
            'type' => AudienceType::STATIC,
            'total_members' => 0,
        ]);
    }
}
