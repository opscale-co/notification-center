<?php

namespace Opscale\NotificationCenter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Opscale\NotificationCenter\Models\Enums\DeliveryStatus;
use Opscale\NotificationCenter\Notifications\NovaNotification;
use Opscale\NotificationCenter\Services\Actions\ForceDelivery;
use PHPUnit\Framework\Attributes\Test;

class ForceDeliveryTest extends ActionTestCase
{
    use RefreshDatabase;

    #[Test]
    public function positive_it_resends_the_delivery_through_its_configured_channel()
    {
        NotificationFacade::fake();

        $profile = $this->makeProfile();
        $delivery = $this->makeDelivery(['profile' => $profile, 'channel' => 'nova', 'status' => DeliveryStatus::FAILED]);

        $result = ForceDelivery::run(['delivery' => $delivery]);

        $this->assertTrue($result->isSuccess());
        NotificationFacade::assertSentTo($profile, NovaNotification::class);
    }

    #[Test]
    public function negative_it_fails_when_the_channel_has_no_configured_notification_class()
    {
        NotificationFacade::fake();

        $delivery = $this->makeDelivery(['channel' => 'carrier-pigeon']);

        $result = ForceDelivery::run(['delivery' => $delivery]);

        $this->assertTrue($result->isFail());
        $this->assertStringContainsString('carrier-pigeon', (string) $result->message());
        NotificationFacade::assertNothingSent();
    }

    #[Test]
    public function false_negative_it_fails_for_an_unknown_delivery_without_sending()
    {
        NotificationFacade::fake();

        $result = ForceDelivery::run(['delivery' => 'non-existent-id']);

        $this->assertTrue($result->isFail());
        NotificationFacade::assertNothingSent();
    }
}
