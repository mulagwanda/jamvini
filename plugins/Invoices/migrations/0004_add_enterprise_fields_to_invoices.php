<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'currency')) {
                $table->string('currency', 3)->nullable()->after('invoice_number');
            }
            if (!Schema::hasColumn('invoices', 'source')) {
                $table->string('source')->nullable()->after('status');
            }
            if (!Schema::hasColumn('invoices', 'external_id')) {
                $table->string('external_id')->nullable()->after('source');
            }
            if (!Schema::hasColumn('invoices', 'discount')) {
                $table->decimal('discount', 15, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('invoices', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('invoices', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('invoices', 'payment_terms')) {
                $table->text('payment_terms')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('invoices', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('payment_terms');
            }
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_items', 'type')) {
                $table->string('type')->default('custom')->after('service_id');
            }
            if (!Schema::hasColumn('invoice_items', 'domain')) {
                $table->string('domain')->nullable()->after('description');
            }
            if (!Schema::hasColumn('invoice_items', 'billing_cycle')) {
                $table->string('billing_cycle')->nullable()->after('total');
            }
            if (!Schema::hasColumn('invoice_items', 'period_start')) {
                $table->date('period_start')->nullable()->after('billing_cycle');
            }
            if (!Schema::hasColumn('invoice_items', 'period_end')) {
                $table->date('period_end')->nullable()->after('period_start');
            }
            if (!Schema::hasColumn('invoice_items', 'metadata')) {
                $table->json('metadata')->nullable()->after('period_end');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            foreach (['metadata', 'period_end', 'period_start', 'billing_cycle', 'domain', 'type'] as $column) {
                if (Schema::hasColumn('invoice_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            foreach (['admin_notes', 'payment_terms', 'cancelled_at', 'sent_at', 'discount', 'external_id', 'source', 'currency'] as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
