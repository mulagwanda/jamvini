<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            if (!Schema::hasColumn('domains', 'registrar_domain_id')) {
                $table->string('registrar_domain_id')->nullable()->after('registrar');
            }
            if (!Schema::hasColumn('domains', 'registrar_statuses')) {
                $table->json('registrar_statuses')->nullable()->after('status');
            }
            if (!Schema::hasColumn('domains', 'registrar_lock')) {
                $table->boolean('registrar_lock')->default(false)->after('registrar_statuses');
            }
            if (!Schema::hasColumn('domains', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('registrar_lock');
            }
            if (!Schema::hasColumn('domains', 'registrar_meta')) {
                $table->json('registrar_meta')->nullable()->after('last_synced_at');
            }
        });

        if (!Schema::hasTable('domain_registrar_operations')) {
            Schema::create('domain_registrar_operations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('domain_id')->nullable()->constrained('domains')->nullOnDelete();
                $table->string('registrar_slug', 64);
                $table->string('domain_name');
                $table->string('operation', 64);
                $table->string('status', 32)->default('pending');
                $table->string('epp_code', 16)->nullable();
                $table->string('client_transaction_id')->nullable();
                $table->string('server_transaction_id')->nullable();
                $table->text('message')->nullable();
                $table->json('request_payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['registrar_slug', 'operation', 'status'], 'tzreg_ops_status_idx');
                $table->index('domain_name', 'tzreg_ops_domain_idx');
            });
        } else {
            $this->ensureIndex('domain_registrar_operations', 'tzreg_ops_status_idx', ['registrar_slug', 'operation', 'status']);
            $this->ensureIndex('domain_registrar_operations', 'tzreg_ops_domain_idx', ['domain_name']);
        }

        if (!Schema::hasTable('domain_pricing_sync_logs')) {
            Schema::create('domain_pricing_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->string('registrar_slug', 64);
                $table->string('status', 32)->default('pending');
                $table->unsignedInteger('updated_count')->default(0);
                $table->text('message')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_pricing_sync_logs');
        Schema::dropIfExists('domain_registrar_operations');

        Schema::table('domains', function (Blueprint $table) {
            foreach (['registrar_domain_id', 'registrar_statuses', 'registrar_lock', 'last_synced_at', 'registrar_meta'] as $column) {
                if (Schema::hasColumn('domains', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    protected function ensureIndex(string $tableName, string $indexName, array $columns): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    protected function indexExists(string $tableName, string $indexName): bool
    {
        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'sqlite') {
                $indexes = DB::select("PRAGMA index_list('{$tableName}')");

                return collect($indexes)->contains(fn ($index) => ($index->name ?? null) === $indexName);
            }

            if ($driver === 'mysql') {
                return !empty(DB::select("SHOW INDEX FROM `{$tableName}` WHERE Key_name = ?", [$indexName]));
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }
};
