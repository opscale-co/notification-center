<?php

namespace Opscale\NotificationCenter\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Schema;
use Opscale\NotificationCenter\Jobs\ExecuteNotificationStrategy;
use Opscale\NotificationCenter\Models\Audience;
use Opscale\NotificationCenter\Models\Enums\AudienceType;
use Opscale\NotificationCenter\Models\Enums\DeliveryStatus;
use Opscale\NotificationCenter\Models\Enums\NotificationType;
use Opscale\NotificationCenter\Models\Notification;
use Opscale\NotificationCenter\Models\Profile;
use PHPUnit\Framework\Attributes\Test;
use Workbench\App\Models\User;

class SendToProfileTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_delivers_to_a_single_profile_without_touching_others()
    {
        NotificationFacade::fake();

        $notification = $this->makeNotification();
        $target = $this->makeProfile();
        $other = $this->makeProfile();

        ExecuteNotificationStrategy::dispatchSync($notification, $target);

        // The 'transactional' strategy's first channel is 'nova'.
        $this->assertDatabaseHas('notification_center_deliveries', [
            'profile_id' => $target->id,
            'notification_id' => $notification->id,
            'channel' => 'nova',
            'status' => DeliveryStatus::PENDING->value,
        ]);

        $this->assertDatabaseMissing('notification_center_deliveries', [
            'profile_id' => $other->id,
        ]);
    }

    #[Test]
    public function it_still_resolves_targets_via_audiences_when_no_profile_is_given()
    {
        NotificationFacade::fake();

        $notification = $this->makeNotification();
        $profile = $this->makeProfile();

        $audience = Audience::create([
            'name' => 'Default',
            'description' => 'Default audience',
            'type' => AudienceType::STATIC->value,
            'total_members' => 1,
        ]);
        $audience->profiles()->attach($profile);
        $notification->audiences()->attach($audience);

        ExecuteNotificationStrategy::dispatchSync($notification);

        $this->assertDatabaseHas('notification_center_deliveries', [
            'profile_id' => $profile->id,
            'notification_id' => $notification->id,
            'channel' => 'nova',
        ]);
    }

    /**
     * Create the polymorphic `users` table (the profile's notifiable) in-process
     * on the shared test connection. loadLaravelMigrations() cannot be used here:
     * it migrates in a subprocess, which gets its own :memory: database.
     */
    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function makeNotification(): Notification
    {
        return Notification::create([
            'subject' => 'Hello',
            'body' => 'Some body',
            'summary' => 'Some summary',
            'type' => NotificationType::TRANSACTIONAL,
        ]);
    }

    protected function makeProfile(): Profile
    {
        $user = User::factory()->create();

        return Profile::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
        ]);
    }
}
