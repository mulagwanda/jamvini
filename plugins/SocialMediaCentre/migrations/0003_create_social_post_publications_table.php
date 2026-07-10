<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_post_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('social_posts')->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('social_accounts')->nullOnDelete();
            $table->string('platform');
            $table->string('mode')->default('manual');
            $table->string('status')->default('pending');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('provider_post_id')->nullable();
            $table->string('provider_url')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('notes')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['post_id', 'platform']);
            $table->index(['platform', 'status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_post_publications');
    }
};
