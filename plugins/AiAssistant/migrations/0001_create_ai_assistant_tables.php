<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_assistant_sources', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('manual');
            $table->string('title');
            $table->string('url')->nullable();
            $table->string('file_path')->nullable();
            $table->longText('content')->nullable();
            $table->longText('indexed_text')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('last_indexed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ai_assistant_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('public_token', 64)->unique();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('visitor_name')->nullable();
            $table->string('visitor_email')->nullable();
            $table->string('status')->default('open');
            $table->unsignedBigInteger('support_ticket_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_assistant_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_assistant_conversations')->cascadeOnDelete();
            $table->string('role');
            $table->longText('message');
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_assistant_messages');
        Schema::dropIfExists('ai_assistant_conversations');
        Schema::dropIfExists('ai_assistant_sources');
    }
};
