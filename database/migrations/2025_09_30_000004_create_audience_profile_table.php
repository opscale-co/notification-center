<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_center_audience_profile', function (Blueprint $table) {
            $table->foreignUlid('audience_id')->constrained('notification_center_audiences')->cascadeOnDelete();
            $table->foreignUlid('profile_id')->constrained('notification_center_profiles')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['audience_id', 'profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_center_audience_profile');
    }
};
