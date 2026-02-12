<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_center_notifications', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('template_id')->nullable()->constrained('dynamic_resources_templates')->nullOnDelete();
            $table->string('subject', 50);
            $table->longText('body');
            $table->string('summary', 100);
            $table->timestamp('expiration')->nullable();
            $table->string('action', 255)->nullable();
            $table->enum('status', ['Draft', 'Published'])->default('Draft');
            $table->enum('type', ['Marketing', 'Transactional', 'System', 'Alert', 'Reminder'])->default('Transactional');
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_center_notifications');
    }
};
