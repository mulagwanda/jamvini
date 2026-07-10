<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('server_packages')) {
            Schema::create('server_packages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('server_id')->constrained('servers')->cascadeOnDelete();
                $table->string('name');
                $table->string('display_name')->nullable();
                $table->json('limits')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
                $table->unique(['server_id', 'name']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('server_packages');
    }
};
