<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->longText('html')->nullable();
            $table->longText('css')->nullable();
            $table->json('blocks')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('author_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('template')->default('default');
            $table->string('featured_image')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cms_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('author_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('featured_image')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cms_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('post');
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('cms_categories')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cms_post_category', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained('cms_posts')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('cms_categories')->cascadeOnDelete();
            $table->primary(['post_id', 'category_id']);
        });

        if (!Schema::hasTable('cms_media')) {
            Schema::create('cms_media', function (Blueprint $table) {
                $table->id();
                $table->string('filename');
                $table->string('original_name');
                $table->string('mime_type');
                $table->integer('size');
                $table->string('path');
                $table->string('thumbnail_path')->nullable();
                $table->string('folder')->default('general');
                $table->string('alt_text')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('admins')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_post_category');
        Schema::dropIfExists('cms_categories');
        Schema::dropIfExists('cms_posts');
        Schema::dropIfExists('cms_pages');
    }
};
