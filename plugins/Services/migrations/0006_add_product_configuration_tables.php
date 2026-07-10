<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'badge_label')) {
                $table->string('badge_label', 40)->nullable()->after('description');
            }
            if (!Schema::hasColumn('services', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            }
        });

        if (!Schema::hasTable('service_custom_fields')) {
            Schema::create('service_custom_fields', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->string('name');
                $table->string('label');
                $table->string('type')->default('text');
                $table->text('options')->nullable();
                $table->string('placeholder')->nullable();
                $table->text('help_text')->nullable();
                $table->boolean('is_required')->default(false);
                $table->boolean('is_public')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['service_id', 'name']);
            });
        }

        if (!Schema::hasTable('service_addons')) {
            Schema::create('service_addons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 15, 2)->default(0);
                $table->string('billing_cycle')->default('same_as_parent');
                $table->boolean('is_required')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_addons');
        Schema::dropIfExists('service_custom_fields');

        Schema::table('services', function (Blueprint $table) {
            foreach (['is_featured', 'badge_label'] as $column) {
                if (Schema::hasColumn('services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
