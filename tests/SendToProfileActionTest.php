<?php

namespace Opscale\NotificationCenter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Validation\ValidationException;
use Opscale\NotificationCenter\Jobs\ExecuteNotificationStrategy;
use Opscale\NotificationCenter\Models\Enums\NotificationStatus;
use Opscale\NotificationCenter\Services\Actions\SendToProfile;
use PHPUnit\Framework\Attributes\Test;

class SendToProfileActionTest extends ActionTestCase
{
    use RefreshDatabase;

    #[Test]
    public function positive_it_publishes_and_dispatches_the_strategy_for_the_profile()
    {
        Bus::fake();

        $notification = $this->makeNotification(['status' => NotificationStatus::DRAFT]);
        $profile = $this->makeProfile();

        $result = SendToProfile::run([
            'notification' => $notification,
            'profile_id' => $profile->id,
        ]);

        $this->assertTrue($result->isSuccess());
        $this->assertSame(NotificationStatus::PUBLISHED, $notification->fresh()->status);
        Bus::assertDispatched(
            ExecuteNotificationStrategy::class,
            fn (ExecuteNotificationStrategy $job): bool => true,
        );
    }

    #[Test]
    public function false_negative_it_rejects_an_unknown_profile()
    {
        Bus::fake();

        $this->expectException(ValidationException::class);

        SendToProfile::run([
            'notification' => $this->makeNotification(),
            'profile_id' => 'non-existent-id',
        ]);
    }
}
