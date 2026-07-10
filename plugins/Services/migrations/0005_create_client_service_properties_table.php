<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_service_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_service_id')->constrained('client_services')->cascadeOnDelete();
            $table->string('key');
            $table->string('label')->nullable();
            $table->text('value')->nullable();
            $table->string('type')->default('text');
            $table->boolean('is_sensitive')->default(false);
            $table->boolean('is_public')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['client_service_id', 'key']);
            $table->index(['key', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_service_properties');
    }
};
