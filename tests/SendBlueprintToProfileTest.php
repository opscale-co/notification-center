<?php

namespace Opscale\NotificationCenter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Opscale\NotificationCenter\Jobs\ExecuteNotificationStrategy;
use Opscale\NotificationCenter\Models\Enums\NotificationStatus;
use Opscale\NotificationCenter\Models\Notification;
use Opscale\NotificationCenter\Services\Actions\SendBlueprintToProfile;
use PHPUnit\Framework\Attributes\Test;

class SendBlueprintToProfileTest extends ActionTestCase
{
    use RefreshDatabase;

    #[Test]
    public function positive_it_builds_a_notification_from_the_blueprint_and_sends_it()
    {
        Bus::fake();

        $blueprint = $this->makeBlueprint(['subject' => 'Hi {{ name }}']);
        $profile = $this->makeProfile();

        $result = SendBlueprintToProfile::run([
            'blueprint' => $blueprint,
            'profile_id' => $profile->id,
            'variables' => ['name' => 'Nelson'],
        ]);

        $this->assertTrue($result->isSuccess());

        $notification = Notification::where('blueprint_id', $blueprint->id)->firstOrFail();
        $this->assertSame('Hi Nelson', $notification->subject);
        $this->assertSame(NotificationStatus::PUBLISHED, $notification->status);
        Bus::assertDispatched(ExecuteNotificationStrategy::class);
    }

    #[Test]
    public function false_negative_it_fails_when_the_resulting_notification_is_invalid()
    {
        Bus::fake();

        // subject resolves to an empty string → Notification validation rejects it.
        $blueprint = $this->makeBlueprint(['subject' => '{{ title }}']);
        $profile = $this->makeProfile();

        $result = SendBlueprintToProfile::run([
            'blueprint' => $blueprint,
            'profile_id' => $profile->id,
            'variables' => [],
        ]);

        $this->assertTrue($result->isFail());
        $this->assertStringContainsString('invalid', (string) $result->message());
        $this->assertDatabaseMissing('notification_center_notifications', [
            'blueprint_id' => $blueprint->id,
        ]);
        Bus::assertNotDispatched(ExecuteNotificationStrategy::class);
    }
}
