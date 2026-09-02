<?php

namespace Opscale\NotificationCenter\Tests;

use PHPUnit\Framework\Attributes\Test;

class ToolControllerTest extends TestCase
{
    #[Test]
    public function it_can_return_a_response()
    {
        $this
            ->get('nova-vendor/opscale-co/notification-center/test-case')
            ->assertStatus(403);
    }
}
