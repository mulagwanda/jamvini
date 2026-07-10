<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_pages', 'excerpt')) {
                $table->text('excerpt')->nullable()->after('slug');
            }

            if (!Schema::hasColumn('cms_pages', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('featured_image');
            }

            if (!Schema::hasColumn('cms_pages', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
        });

        Schema::table('cms_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_posts', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('featured_image');
            }

            if (!Schema::hasColumn('cms_posts', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cms_posts', function (Blueprint $table) {
            if (Schema::hasColumn('cms_posts', 'meta_description')) {
                $table->dropColumn('meta_description');
            }

            if (Schema::hasColumn('cms_posts', 'meta_title')) {
                $table->dropColumn('meta_title');
            }
        });

        Schema::table('cms_pages', function (Blueprint $table) {
            if (Schema::hasColumn('cms_pages', 'meta_description')) {
                $table->dropColumn('meta_description');
            }

            if (Schema::hasColumn('cms_pages', 'meta_title')) {
                $table->dropColumn('meta_title');
            }

            if (Schema::hasColumn('cms_pages', 'excerpt')) {
                $table->dropColumn('excerpt');
            }
        });
    }
};
