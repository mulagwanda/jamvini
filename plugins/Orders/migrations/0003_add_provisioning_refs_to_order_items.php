<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'client_service_id')) {
                $table->unsignedBigInteger('client_service_id')->nullable()->after('service_id');
            }
            if (!Schema::hasColumn('order_items', 'domain_id')) {
                $table->unsignedBigInteger('domain_id')->nullable()->after('client_service_id');
            }
            if (!Schema::hasColumn('order_items', 'provisioned_at')) {
                $table->timestamp('provisioned_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('order_items', 'provisioning_notes')) {
                $table->text('provisioning_notes')->nullable()->after('provisioned_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            foreach (['provisioning_notes', 'provisioned_at', 'domain_id', 'client_service_id'] as $column) {
                if (Schema::hasColumn('order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
