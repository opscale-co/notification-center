<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_center_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('delivery_id')->constrained('notification_center_deliveries')->cascadeOnDelete();
            $table->string('name');
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_center_events');
    }
};
