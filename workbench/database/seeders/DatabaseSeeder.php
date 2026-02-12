<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Workbench\App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@laravel.com',
        ]);

        User::factory()->create([
            'name' => 'Laravel Nova',
            'email' => 'nova@laravel.com',
        ]);

        User::factory()->count(8)->create();

        $this->call(AlertTemplateSeeder::class);
        $this->call(ProfileAudienceSeeder::class);
    }
}
