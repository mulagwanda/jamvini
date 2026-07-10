<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('whmcs_migration_batches')) {
            Schema::create('whmcs_migration_batches', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('status')->default('uploaded');
                $table->string('source_type')->default('archive');
                $table->string('file_path')->nullable();
                $table->json('summary')->nullable();
                $table->json('mapping')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whmcs_migration_batches');
    }
};
