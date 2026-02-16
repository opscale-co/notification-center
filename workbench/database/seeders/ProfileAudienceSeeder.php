<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Opscale\NotificationCenter\Models\Audience;
use Opscale\NotificationCenter\Models\Enums\AudienceType;
use Opscale\NotificationCenter\Models\Profile;
use Opscale\NotificationCenter\Models\Subscription;
use Workbench\App\Models\User;

class ProfileAudienceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@laravel.com')->first();

        $profile = Profile::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->id,
        ]);

        Subscription::create([
            'profile_id' => $profile->id,
            'type' => 'nova',
            'contact' => $admin->id,
            'verified' => true,
            'priority' => 1,
        ]);

        $audience = Audience::create([
            'name' => 'Default',
            'description' => 'Default audience for all users',
            'type' => AudienceType::STATIC->value,
            'total_members' => 1,
        ]);

        $audience->profiles()->attach($profile);

        Audience::create([
            'name' => 'Active Users',
            'description' => 'Dynamic audience based on recent activity',
            'type' => AudienceType::DYNAMIC->value,
            'criteria' => 'SELECT id FROM notification_center_profiles WHERE deleted_at IS NULL',
        ]);
    }
}
