<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin_departments')) {
            Schema::create('admin_departments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'admin_department_id')) {
                $table->foreignId('admin_department_id')->nullable()->after('id')->constrained('admin_departments')->nullOnDelete();
            }
            if (!Schema::hasColumn('admins', 'status')) {
                $table->string('status')->default('active')->after('role');
            }
            if (!Schema::hasColumn('admins', 'job_title')) {
                $table->string('job_title')->nullable()->after('status');
            }
            if (!Schema::hasColumn('admins', 'phone')) {
                $table->string('phone')->nullable()->after('job_title');
            }
            if (!Schema::hasColumn('admins', 'permissions')) {
                $table->json('permissions')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('admins', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('permissions');
            }
            if (!Schema::hasColumn('admins', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            foreach (['last_login_ip', 'last_login_at', 'permissions', 'phone', 'job_title', 'status'] as $column) {
                if (Schema::hasColumn('admins', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('admins', 'admin_department_id')) {
                $table->dropConstrainedForeignId('admin_department_id');
            }
        });

        Schema::dropIfExists('admin_departments');
    }
};
