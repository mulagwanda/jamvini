<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            if (!Schema::hasColumn('slides', 'button2_text')) {
                $table->string('button2_text')->nullable()->after('button_link');
            }
            if (!Schema::hasColumn('slides', 'button2_link')) {
                $table->string('button2_link')->nullable()->after('button2_text');
            }
            if (!Schema::hasColumn('slides', 'overlay_color')) {
                $table->string('overlay_color')->nullable()->after('button2_link');
            }
            if (!Schema::hasColumn('slides', 'text_color')) {
                $table->string('text_color')->nullable()->after('overlay_color');
            }
            if (!Schema::hasColumn('slides', 'background_position')) {
                $table->string('background_position')->default('center center')->after('text_color');
            }
            if (!Schema::hasColumn('slides', 'content_width')) {
                $table->string('content_width')->default('720px')->after('background_position');
            }
            if (!Schema::hasColumn('slides', 'animation')) {
                $table->string('animation')->default('fade-up')->after('content_width');
            }
        });
    }

    public function down(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            foreach (['animation', 'content_width', 'background_position', 'text_color', 'overlay_color', 'button2_link', 'button2_text'] as $column) {
                if (Schema::hasColumn('slides', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
