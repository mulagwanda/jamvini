<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            if (!Schema::hasColumn('slides', 'layers')) {
                $table->json('layers')->nullable()->after('animation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('slides', function (Blueprint $table) {
            if (Schema::hasColumn('slides', 'layers')) {
                $table->dropColumn('layers');
            }
        });
    }
};
