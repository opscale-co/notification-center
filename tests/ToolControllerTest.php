<?php

namespace Opscale\NotificationCenter\Tests;

class ToolControllerTest extends TestCase
{
    /** @test */
    public function it_can_return_a_response()
    {
        $this
            ->get('nova-vendor/opscale-co/notification-center/test-case')
            ->assertStatus(403);
    }
}
