<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_center_deliveries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('profile_id');
            $table->ulid('notification_id');
            $table->string('channel');
            $table->enum('status', ['Pending', 'Failed', 'Sent', 'Received', 'Opened', 'Verified', 'Expired'])->default('Pending');
            $table->string('open_slug')->unique()->nullable()->index();
            $table->string('action_slug')->unique()->nullable()->index();
            $table->timestamps();

            $table->unique(['profile_id', 'notification_id', 'channel']);
            $table->foreign('profile_id')->references('id')->on('notification_center_profiles')->onDelete('cascade');
            $table->foreign('notification_id')->references('id')->on('notification_center_notifications')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_center_deliveries');
    }
};
