<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seo_meta')) {
            Schema::create('seo_meta', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type');
                $table->unsignedBigInteger('entity_id');
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->string('meta_keywords')->nullable();
                $table->string('og_title')->nullable();
                $table->text('og_description')->nullable();
                $table->string('og_image')->nullable();
                $table->string('canonical_url')->nullable();
                $table->boolean('no_index')->default(false);
                $table->boolean('no_follow')->default(false);
                $table->timestamps();
                $table->unique(['entity_type', 'entity_id']);
            });
        }

        if (!Schema::hasTable('seo_settings')) {
            Schema::create('seo_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('seo_analytics_events')) {
            Schema::create('seo_analytics_events', function (Blueprint $table) {
                $table->id();
                $table->uuid('visitor_id')->nullable()->index();
                $table->string('session_id', 120)->nullable()->index();
                $table->string('event_type', 40)->default('pageview')->index();
                $table->string('url', 2048);
                $table->string('path', 1024)->nullable();
                $table->string('path_hash', 64)->nullable()->index('seo_events_path_hash_idx');
                $table->string('title')->nullable();
                $table->string('referrer', 2048)->nullable();
                $table->string('utm_source')->nullable()->index();
                $table->string('utm_medium')->nullable();
                $table->string('utm_campaign')->nullable()->index();
                $table->string('device_type', 40)->nullable();
                $table->string('browser', 80)->nullable();
                $table->string('country', 80)->nullable();
                $table->string('ip_hash', 64)->nullable()->index();
                $table->json('metadata')->nullable();
                $table->timestamp('occurred_at')->index();
                $table->timestamps();

                $table->index(['event_type', 'occurred_at']);
                $table->index(['path_hash', 'occurred_at'], 'seo_events_path_time_idx');
            });
        }

        if (Schema::hasTable('seo_analytics_events')) {
            Schema::table('seo_analytics_events', function (Blueprint $table) {
                if (!Schema::hasColumn('seo_analytics_events', 'path_hash')) {
                    $table->string('path_hash', 64)->nullable()->after('path');
                    $table->index('path_hash', 'seo_events_path_hash_idx');
                    $table->index(['path_hash', 'occurred_at'], 'seo_events_path_time_idx');
                }
            });
        }

        if (!Schema::hasTable('seo_content_audits')) {
            Schema::create('seo_content_audits', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type');
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->string('url', 2048)->nullable();
                $table->unsignedTinyInteger('score')->default(0);
                $table->json('checks')->nullable();
                $table->timestamp('audited_at')->nullable();
                $table->timestamps();
                $table->index(['entity_type', 'entity_id']);
            });
        }

        if (!Schema::hasTable('seo_redirects')) {
            Schema::create('seo_redirects', function (Blueprint $table) {
                $table->id();
                $table->string('from_path', 1024);
                $table->string('from_hash', 64)->unique('seo_redir_hash_uq');
                $table->string('to_url', 2048);
                $table->unsignedSmallInteger('status_code')->default(301);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('hits')->default(0);
                $table->timestamp('last_hit_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('seo_redirects') && !Schema::hasColumn('seo_redirects', 'from_hash')) {
            Schema::table('seo_redirects', function (Blueprint $table) {
                $table->string('from_hash', 64)->nullable()->after('from_path');
                $table->unique('from_hash', 'seo_redir_hash_uq');
            });

            if (DB::getDriverName() === 'mysql') {
                DB::statement('update seo_redirects set from_hash = sha2(from_path, 256) where from_hash is null');
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
        Schema::dropIfExists('seo_meta');
    }
};
