<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Services catalog (product definitions)
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained('service_groups')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('setup_fee', 15, 2)->default(0);
            $table->string('billing_cycle')->default('monthly');
            $table->json('pricing')->nullable();
            $table->boolean('is_free')->default(false);
            $table->string('billing_type')->default('recurring');
            $table->json('free_domain_cycles')->nullable();
            $table->json('features')->nullable();
            $table->json('configurable_options')->nullable();
            $table->boolean('upgradable')->default(true);
            $table->boolean('allow_downgrade')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Client services (orders)
        Schema::create('client_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->decimal('price', 15, 2);
            $table->string('billing_cycle')->default('monthly');
            $table->date('next_due_date')->nullable();
            $table->date('registered_date')->nullable();
            $table->string('status')->default('active');
            $table->string('domain')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Configurable options
        Schema::create('service_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('dropdown');
            $table->json('options')->nullable();
            $table->json('prices')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('client_service_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_service_id')->constrained('client_services')->cascadeOnDelete();
            $table->foreignId('service_option_id')->constrained('service_options')->cascadeOnDelete();
            $table->string('value');
            $table->decimal('price', 15, 2)->default(0);
            $table->timestamps();
        });

        // Server linking
        Schema::create('server_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained('servers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('package_name')->nullable();
            $table->json('limits')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Domain TLD configuration (belongs to Services plugin since domains are services)
        Schema::create('domain_tlds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('tld');
            $table->boolean('dns_management')->default(true);
            $table->boolean('email_forwarding')->default(true);
            $table->boolean('id_protection')->default(true);
            $table->boolean('epp_code')->default(true);
            $table->boolean('auto_register')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['service_id', 'tld']);
        });

        Schema::create('domain_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tld_id')->constrained('domain_tlds')->cascadeOnDelete();
            $table->integer('years')->default(1);
            $table->decimal('register_price', 15, 2)->default(0);
            $table->decimal('renewal_price', 15, 2)->default(0);
            $table->decimal('transfer_price', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tld_id', 'years']);
        });

        Schema::create('domain_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tld_id')->constrained('domain_tlds')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('boolean');
            $table->decimal('price', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('domain_period_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tld_id')->constrained('domain_tlds')->cascadeOnDelete();
            $table->integer('period_type');
            $table->integer('days')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_period_pricing');
        Schema::dropIfExists('domain_addons');
        Schema::dropIfExists('domain_pricing');
        Schema::dropIfExists('domain_tlds');
        Schema::dropIfExists('server_service');
        Schema::dropIfExists('client_service_options');
        Schema::dropIfExists('service_options');
        Schema::dropIfExists('client_services');
        Schema::dropIfExists('services');
    }
};