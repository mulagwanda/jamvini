<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'gateway_slug')) {
                $table->string('gateway_slug')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('transactions', 'gateway_type')) {
                $table->string('gateway_type')->default('offline')->after('gateway_slug');
            }
            if (!Schema::hasColumn('transactions', 'currency')) {
                $table->string('currency', 3)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('transactions', 'fee_amount')) {
                $table->decimal('fee_amount', 15, 2)->default(0)->after('currency');
            }
            if (!Schema::hasColumn('transactions', 'refunded_amount')) {
                $table->decimal('refunded_amount', 15, 2)->default(0)->after('fee_amount');
            }
            if (!Schema::hasColumn('transactions', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('transactions', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('transactions', 'metadata')) {
                $table->json('metadata')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            foreach (['gateway_slug', 'gateway_type', 'currency', 'fee_amount', 'refunded_amount', 'paid_at', 'refunded_at', 'metadata'] as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
