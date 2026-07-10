<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('client_groups')) {
            Schema::create('client_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->string('color', 20)->default('#6C5CE7');
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->boolean('is_default')->default(false);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('clients') && !Schema::hasColumn('clients', 'client_group_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->foreignId('client_group_id')->nullable()->after('type')->constrained('client_groups')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('client_contacts')) {
            Schema::create('client_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
                $table->string('name');
                $table->string('role')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->boolean('receives_billing')->default(false);
                $table->boolean('receives_support')->default(false);
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('client_groups') && DB::table('client_groups')->count() === 0) {
            DB::table('client_groups')->insert([
                ['name' => 'Standard', 'slug' => 'standard', 'color' => '#64748B', 'discount_percent' => 0, 'is_default' => true, 'description' => 'Default client group.', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'VIP', 'slug' => 'vip', 'color' => '#7C3AED', 'discount_percent' => 0, 'is_default' => false, 'description' => 'High value managed accounts.', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Reseller', 'slug' => 'reseller', 'color' => '#0891B2', 'discount_percent' => 0, 'is_default' => false, 'description' => 'Partner and reseller clients.', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Government', 'slug' => 'government', 'color' => '#16A34A', 'discount_percent' => 0, 'is_default' => false, 'description' => 'Government and public sector clients.', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_contacts');

        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'client_group_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropConstrainedForeignId('client_group_id');
            });
        }

        Schema::dropIfExists('client_groups');
    }
};
