<?php

namespace Opscale\NotificationCenter\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Opscale\NotificationCenter\Models\Audience;
use Opscale\NotificationCenter\Models\Enums\AudienceType;
use Opscale\NotificationCenter\Services\Actions\CreateAudience;
use PHPUnit\Framework\Attributes\Test;

class CreateAudienceTest extends ActionTestCase
{
    use RefreshDatabase;

    #[Test]
    public function positive_it_creates_a_static_audience_from_the_selected_profiles()
    {
        $first = $this->makeProfile();
        $second = $this->makeProfile();

        $result = CreateAudience::run([
            'name' => 'VIP',
            'description' => 'The important ones',
            'profiles' => new Collection([$first, $second]),
        ]);

        $this->assertTrue($result->isSuccess());

        $audience = Audience::where('name', 'VIP')->firstOrFail();
        $this->assertSame(AudienceType::STATIC, $audience->type);
        $this->assertSame(2, $audience->total_members);
        $this->assertEqualsCanonicalizing(
            [$first->id, $second->id],
            $audience->profiles()->pluck('notification_center_profiles.id')->all(),
        );
    }

    #[Test]
    public function false_positive_it_only_attaches_the_selected_profiles()
    {
        $selected = $this->makeProfile();
        $other = $this->makeProfile();

        $result = CreateAudience::run([
            'name' => 'Subset',
            'description' => 'Only the selected ones',
            'profiles' => new Collection([$selected]),
        ]);

        $this->assertTrue($result->isSuccess());

        $audience = Audience::where('name', 'Subset')->firstOrFail();
        $attached = $audience->profiles()->pluck('notification_center_profiles.id')->all();
        $this->assertContains($selected->id, $attached);
        $this->assertNotContains($other->id, $attached);
    }

    #[Test]
    public function false_negative_it_rejects_creation_without_a_name()
    {
        $this->expectException(ValidationException::class);

        CreateAudience::run([
            'profiles' => new Collection([$this->makeProfile()]),
        ]);
    }
}
