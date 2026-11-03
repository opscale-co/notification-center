<?php

namespace Opscale\NotificationCenter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Validation\ValidationException;
use Opscale\NotificationCenter\Jobs\ExecuteNotificationStrategy;
use Opscale\NotificationCenter\Models\Enums\NotificationStatus;
use Opscale\NotificationCenter\Services\Actions\PublishNotification;
use PHPUnit\Framework\Attributes\Test;

class PublishNotificationTest extends ActionTestCase
{
    use RefreshDatabase;

    #[Test]
    public function positive_it_publishes_the_notification_to_the_audience()
    {
        Bus::fake();

        $notification = $this->makeNotification(['status' => NotificationStatus::DRAFT]);
        $audience = $this->makeAudience();

        $result = PublishNotification::run([
            'notification' => $notification,
            'audience_id' => $audience->id,
        ]);

        $this->assertTrue($result->isSuccess());
        $this->assertSame(NotificationStatus::PUBLISHED, $notification->fresh()->status);
        $this->assertContains(
            $audience->id,
            $notification->audiences()->pluck('notification_center_audiences.id')->all(),
        );
        Bus::assertDispatched(ExecuteNotificationStrategy::class);
    }

    #[Test]
    public function false_negative_it_rejects_an_unknown_audience()
    {
        Bus::fake();

        $this->expectException(ValidationException::class);

        PublishNotification::run([
            'notification' => $this->makeNotification(),
            'audience_id' => 'non-existent-id',
        ]);
    }
}
