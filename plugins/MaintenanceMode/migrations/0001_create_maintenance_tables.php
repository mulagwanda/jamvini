<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('maintenance_settings')) {
            Schema::create('maintenance_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('maintenance_events')) {
            Schema::create('maintenance_events', function (Blueprint $table) {
                $table->id();
                $table->string('type')->index();
                $table->string('title')->nullable();
                $table->text('message')->nullable();
                $table->timestamp('scheduled_start_at')->nullable();
                $table->timestamp('scheduled_end_at')->nullable();
                $table->string('status')->default('scheduled')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_events');
        Schema::dropIfExists('maintenance_settings');
    }
};
