<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_media')) {
            return;
        }

        Schema::create('cms_media', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type');
            $table->integer('size');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->string('folder')->default('general');
            $table->string('source')->default('upload');
            $table->string('external_id')->nullable();
            $table->json('attribution')->nullable();
            $table->json('metadata')->nullable();
            $table->string('alt_text')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Kept intentionally for compatibility with older CMS installs.
    }
};
