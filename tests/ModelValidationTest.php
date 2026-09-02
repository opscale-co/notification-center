<?php

namespace Opscale\NotificationCenter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Opscale\NotificationCenter\Models\Notification;
use PHPUnit\Framework\Attributes\Test;

class ModelValidationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_rejects_an_invalid_notification_on_save()
    {
        $this->expectException(ValidationException::class);

        Notification::create([
            'body' => 'Some body',
            'summary' => 'Some summary',
        ]);
    }

    #[Test]
    public function it_saves_a_valid_notification()
    {
        $notification = Notification::create([
            'subject' => 'Hello',
            'body' => 'Some body',
            'summary' => 'Some summary',
        ]);

        $this->assertTrue($notification->exists);
        $this->assertDatabaseHas('notification_center_notifications', [
            'subject' => 'Hello',
        ]);
    }
}
