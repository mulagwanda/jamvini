<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 3)->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'source')) {
                $table->string('source')->nullable()->after('currency');
            }
            if (!Schema::hasColumn('orders', 'external_id')) {
                $table->string('external_id')->nullable()->after('source');
            }
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('external_id');
            }
            if (!Schema::hasColumn('orders', 'provisioning_status')) {
                $table->string('provisioning_status')->default('not_started')->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'ordered_at')) {
                $table->timestamp('ordered_at')->nullable()->after('provisioning_status');
            }
            if (!Schema::hasColumn('orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('accepted_at');
            }
            if (!Schema::hasColumn('orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('orders', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            }
            if (!Schema::hasColumn('orders', 'client_notes')) {
                $table->text('client_notes')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('orders', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('client_notes');
            }
            if (!Schema::hasColumn('orders', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('admin_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'ip_address',
                'admin_notes',
                'client_notes',
                'cancellation_reason',
                'cancelled_at',
                'completed_at',
                'ordered_at',
                'provisioning_status',
                'payment_method',
                'external_id',
                'source',
                'currency',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
