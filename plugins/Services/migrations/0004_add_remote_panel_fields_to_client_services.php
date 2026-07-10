<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_services', function (Blueprint $table) {
            if (!Schema::hasColumn('client_services', 'server_id')) {
                $table->unsignedBigInteger('server_id')->nullable()->after('service_id');
            }
            if (!Schema::hasColumn('client_services', 'control_panel')) {
                $table->string('control_panel')->nullable()->after('server_id');
            }
            if (!Schema::hasColumn('client_services', 'remote_username')) {
                $table->string('remote_username')->nullable()->after('control_panel');
            }
            if (!Schema::hasColumn('client_services', 'remote_domain')) {
                $table->string('remote_domain')->nullable()->after('remote_username');
            }
        });
    }

    public function down(): void
    {
        Schema::table('client_services', function (Blueprint $table) {
            foreach (['remote_domain', 'remote_username', 'control_panel', 'server_id'] as $column) {
                if (Schema::hasColumn('client_services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
