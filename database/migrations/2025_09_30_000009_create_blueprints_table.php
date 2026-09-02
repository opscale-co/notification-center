<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_center_blueprints', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('subject');
            $table->text('body');
            $table->text('summary');
            $table->string('action')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('notification_center_notifications', function (Blueprint $table) {
            // Nullable link to the blueprint a notification was generated from.
            // No DB-level foreign key: SQLite cannot add constraints via ALTER TABLE,
            // and blueprints use soft deletes so rows are never hard-removed.
            $table->ulid('blueprint_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('notification_center_notifications', function (Blueprint $table) {
            $table->dropColumn('blueprint_id');
        });

        Schema::dropIfExists('notification_center_blueprints');
    }
};
