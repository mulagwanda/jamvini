<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('custom_fields')) {
            Schema::create('custom_fields', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type', 80)->index();
                $table->string('name', 100);
                $table->string('label');
                $table->string('type', 40)->default('text');
                $table->text('options')->nullable();
                $table->string('placeholder')->nullable();
                $table->text('help_text')->nullable();
                $table->string('default_value')->nullable();
                $table->boolean('is_required')->default(false);
                $table->boolean('is_public')->default(false);
                $table->boolean('show_on_registration')->default(false);
                $table->boolean('show_on_admin_profile')->default(true);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->json('settings')->nullable();
                $table->timestamps();

                $table->unique(['entity_type', 'name'], 'cf_entity_name_unique');
                $table->index(['entity_type', 'is_active', 'sort_order'], 'cf_entity_active_sort_idx');
            });
        }

        if (!Schema::hasTable('custom_field_values')) {
            Schema::create('custom_field_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('custom_field_id')->constrained('custom_fields')->cascadeOnDelete();
                $table->string('entity_type', 80)->index();
                $table->unsignedBigInteger('entity_id')->index();
                $table->longText('value')->nullable();
                $table->timestamps();

                $table->unique(['custom_field_id', 'entity_type', 'entity_id'], 'cfv_field_entity_unique');
                $table->index(['entity_type', 'entity_id'], 'cfv_entity_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
    }
};
