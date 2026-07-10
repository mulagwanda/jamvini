<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'client_number')) {
                $table->string('client_number')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('clients', 'type')) {
                $table->string('type')->default('individual')->after('company_name');
            }
            if (!Schema::hasColumn('clients', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('clients', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('state');
            }
            if (!Schema::hasColumn('clients', 'billing_email')) {
                $table->string('billing_email')->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('clients', 'technical_email')) {
                $table->string('technical_email')->nullable()->after('billing_email');
            }
            if (!Schema::hasColumn('clients', 'currency')) {
                $table->string('currency', 3)->nullable()->after('vat_exempt');
            }
            if (!Schema::hasColumn('clients', 'language')) {
                $table->string('language', 10)->default('en')->after('currency');
            }
            if (!Schema::hasColumn('clients', 'timezone')) {
                $table->string('timezone')->nullable()->after('language');
            }
            if (!Schema::hasColumn('clients', 'credit_balance')) {
                $table->decimal('credit_balance', 12, 2)->default(0)->after('timezone');
            }
            if (!Schema::hasColumn('clients', 'source')) {
                $table->string('source')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('clients', 'external_id')) {
                $table->string('external_id')->nullable()->after('source');
            }
            if (!Schema::hasColumn('clients', 'email_marketing_opt_in')) {
                $table->boolean('email_marketing_opt_in')->default(false)->after('external_id');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('clients', 'client_number') && Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropUnique('clients_client_number_unique');
            });
        }

        Schema::table('clients', function (Blueprint $table) {
            foreach ([
                'email_marketing_opt_in',
                'external_id',
                'source',
                'credit_balance',
                'timezone',
                'language',
                'currency',
                'technical_email',
                'billing_email',
                'postal_code',
                'state',
                'type',
                'client_number',
            ] as $column) {
                if (Schema::hasColumn('clients', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
