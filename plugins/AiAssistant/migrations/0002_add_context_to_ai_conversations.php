<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_assistant_conversations')) {
            return;
        }

        Schema::table('ai_assistant_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_assistant_conversations', 'page_url')) {
                $table->text('page_url')->nullable()->after('visitor_email');
            }
            if (!Schema::hasColumn('ai_assistant_conversations', 'page_title')) {
                $table->string('page_title')->nullable()->after('page_url');
            }
            if (!Schema::hasColumn('ai_assistant_conversations', 'country_code')) {
                $table->string('country_code', 2)->nullable()->after('page_title');
            }
            if (!Schema::hasColumn('ai_assistant_conversations', 'country_name')) {
                $table->string('country_name')->nullable()->after('country_code');
            }
            if (!Schema::hasColumn('ai_assistant_conversations', 'escalated_at')) {
                $table->timestamp('escalated_at')->nullable()->after('support_ticket_id');
            }
            if (!Schema::hasColumn('ai_assistant_conversations', 'last_staff_reply_at')) {
                $table->timestamp('last_staff_reply_at')->nullable()->after('escalated_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('ai_assistant_conversations')) {
            return;
        }

        Schema::table('ai_assistant_conversations', function (Blueprint $table) {
            foreach (['page_url', 'page_title', 'country_code', 'country_name', 'escalated_at', 'last_staff_reply_at'] as $column) {
                if (Schema::hasColumn('ai_assistant_conversations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
