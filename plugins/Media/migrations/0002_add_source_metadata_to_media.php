<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_media', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_media', 'source')) {
                $table->string('source')->default('upload')->after('folder');
            }
            if (!Schema::hasColumn('cms_media', 'external_id')) {
                $table->string('external_id')->nullable()->after('source');
            }
            if (!Schema::hasColumn('cms_media', 'attribution')) {
                $table->json('attribution')->nullable()->after('external_id');
            }
            if (!Schema::hasColumn('cms_media', 'metadata')) {
                $table->json('metadata')->nullable()->after('attribution');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cms_media', function (Blueprint $table) {
            foreach (['metadata', 'attribution', 'external_id', 'source'] as $column) {
                if (Schema::hasColumn('cms_media', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
