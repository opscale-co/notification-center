<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_center_subscriptions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('profile_id')->constrained('notification_center_profiles')->cascadeOnDelete();
            $table->string('type');
            $table->string('contact');
            $table->boolean('verified')->default(false);
            $table->unsignedTinyInteger('priority')->default(5);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['profile_id', 'type', 'contact']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_center_subscriptions');
    }
};
