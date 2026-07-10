<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('security_settings')) {
            Schema::create('security_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('security_events')) {
            Schema::create('security_events', function (Blueprint $table) {
                $table->id();
                $table->string('severity', 20)->default('info')->index();
                $table->string('type', 80)->index();
                $table->string('ip_address', 64)->nullable()->index();
                $table->string('url', 2048)->nullable();
                $table->string('user_agent', 512)->nullable();
                $table->text('message')->nullable();
                $table->json('context')->nullable();
                $table->timestamp('occurred_at')->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('security_ip_rules')) {
            Schema::create('security_ip_rules', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 64)->index();
                $table->string('action', 20)->default('block')->index();
                $table->string('reason')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('security_file_scan_results')) {
            Schema::create('security_file_scan_results', function (Blueprint $table) {
                $table->id();
                $table->string('path', 1024);
                $table->string('status', 40)->default('ok')->index();
                $table->string('hash', 128)->nullable();
                $table->text('message')->nullable();
                $table->timestamp('scanned_at')->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('security_file_scan_results');
        Schema::dropIfExists('security_ip_rules');
        Schema::dropIfExists('security_events');
        Schema::dropIfExists('security_settings');
    }
};
