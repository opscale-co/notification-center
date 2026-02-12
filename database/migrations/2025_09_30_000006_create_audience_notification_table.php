<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_center_audience_notification', function (Blueprint $table) {
            $table->ulid('audience_id');
            $table->ulid('notification_id');

            $table->primary(['audience_id', 'notification_id']);
            $table->foreign('audience_id')->references('id')->on('notification_center_audiences')->onDelete('cascade');
            $table->foreign('notification_id')->references('id')->on('notification_center_notifications')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_center_audience_notification');
    }
};
