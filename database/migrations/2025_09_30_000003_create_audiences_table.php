<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_center_audiences', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->enum('type', ['Static', 'Dynamic', 'Segment']);
            $table->json('criteria')->nullable();
            $table->unsignedInteger('total_members')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_center_audiences');
    }
};
