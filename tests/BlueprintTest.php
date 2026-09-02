<?php

namespace Opscale\NotificationCenter\Tests;

use Illuminate\Database\Schema\Blueprint as SchemaBlueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Schema;
use Opscale\NotificationCenter\Jobs\ExecuteNotificationStrategy;
use Opscale\NotificationCenter\Models\Blueprint;
use Opscale\NotificationCenter\Models\Enums\NotificationType;
use Opscale\NotificationCenter\Models\Notification;
use Opscale\NotificationCenter\Models\Profile;
use PHPUnit\Framework\Attributes\Test;
use Workbench\App\Models\User;

class BlueprintTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_generated_notification_defaults_to_transactional_and_never_expires()
    {
        $attributes = $this->makeBlueprint()->toNotificationAttributes();

        $this->assertSame(NotificationType::TRANSACTIONAL, $attributes['type']);
        $this->assertArrayNotHasKey('expiration', $attributes);
    }

    #[Test]
    public function it_detects_unique_variables_across_all_fields_including_the_action()
    {
        $blueprint = $this->makeBlueprint();

        // name (subject), order_id (body), tracking_code (action); duplicates ignored.
        $this->assertSame(['name', 'order_id', 'tracking_code'], $blueprint->variables());
    }

    #[Test]
    public function it_substitutes_variable_values_including_inside_the_action_url()
    {
        $blueprint = $this->makeBlueprint();

        $attributes = $blueprint->toNotificationAttributes([
            'name' => 'Ada',
            'tracking_code' => 'ABC123',
        ]);

        $this->assertSame('Hi Ada', $attributes['subject']);
        $this->assertSame('Hi Ada, your order  is ready. Thanks Ada!', $attributes['body']);
        $this->assertSame('Order ', $attributes['summary']);
        $this->assertSame('https://example.com/track/ABC123', $attributes['action']);
        $this->assertSame(NotificationType::TRANSACTIONAL, $attributes['type']);
    }

    #[Test]
    public function the_action_is_null_when_the_blueprint_has_none()
    {
        $attributes = $this->makeBlueprint(['action' => null])->toNotificationAttributes(['name' => 'Ada']);

        $this->assertNull($attributes['action']);
    }

    #[Test]
    public function it_creates_a_resolved_notification_linked_to_the_blueprint_and_delivers_it()
    {
        NotificationFacade::fake();

        $blueprint = $this->makeBlueprint();
        $profile = $this->makeProfile();

        $notification = Notification::create($blueprint->toNotificationAttributes([
            'name' => 'Ada',
            'order_id' => '1234',
            'tracking_code' => 'ABC123',
        ]) + ['blueprint_id' => $blueprint->id]);

        ExecuteNotificationStrategy::dispatchSync($notification, $profile);

        // The stored notification has resolved text (no {{ }} left) and links back to the blueprint.
        $this->assertStringNotContainsString('{{', $notification->body);
        $this->assertSame('Hi Ada, your order 1234 is ready. Thanks Ada!', $notification->body);
        $this->assertSame('https://example.com/track/ABC123', $notification->action);
        $this->assertSame($blueprint->id, $notification->blueprint_id);
        $this->assertTrue($notification->blueprint->is($blueprint));

        // Defaults to transactional and never expires.
        $this->assertSame(NotificationType::TRANSACTIONAL, $notification->type);
        $this->assertNull($notification->expiration);

        // The 'transactional' strategy's first channel is 'nova'.
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

    protected function makeBlueprint(array $overrides = []): Blueprint
    {
        return Blueprint::create(array_merge([
            'name' => 'Order update',
            'subject' => 'Hi {{ name }}',
            'body' => 'Hi {{ name }}, your order {{ order_id }} is ready. Thanks {{ name }}!',
            'summary' => 'Order {{ order_id }}',
            'action' => 'https://example.com/track/{{ tracking_code }}',
        ], $overrides));
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
