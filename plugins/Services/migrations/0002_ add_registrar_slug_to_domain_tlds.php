<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domain_tlds', function (Blueprint $table) {
            if (!Schema::hasColumn('domain_tlds', 'registrar_slug')) {
                $table->string('registrar_slug')->nullable()->after('tld');
            }
        });
    }

    public function down(): void
    {
        Schema::table('domain_tlds', function (Blueprint $table) {
            $table->dropColumn('registrar_slug');
        });
    }
};