<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('promotion_type', 24)->default('automatic');
                $table->string('discount_type', 32)->default('percentage');
                $table->decimal('discount_value', 15, 2)->default(0);
                $table->string('applies_to', 24)->default('signup');
                $table->unsignedSmallInteger('recurring_cycles')->nullable();
                $table->string('status', 24)->default('active');
                $table->boolean('stackable')->default(false);
                $table->integer('priority')->default(100);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->json('conditions')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['status', 'promotion_type'], 'promo_status_type_idx');
                $table->index(['priority', 'starts_at', 'ends_at'], 'promo_priority_dates_idx');
            });
        }

        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
                $table->string('code', 80)->unique();
                $table->string('status', 24)->default('active');
                $table->unsignedInteger('max_uses')->nullable();
                $table->unsignedInteger('max_uses_per_client')->nullable();
                $table->decimal('min_cart_total', 15, 2)->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['status', 'starts_at', 'ends_at'], 'coupon_status_dates_idx');
            });
        }

        if (!Schema::hasTable('coupon_redemptions')) {
            Schema::create('coupon_redemptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
                $table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
                $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->string('code', 80)->nullable();
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->string('currency', 8)->nullable();
                $table->string('status', 24)->default('applied');
                $table->string('ip_address', 64)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('redeemed_at')->nullable();
                $table->timestamps();
                $table->index(['coupon_id', 'client_id'], 'redemptions_coupon_client_idx');
                $table->index(['order_id', 'invoice_id'], 'redemptions_order_invoice_idx');
            });
        }

        if (!Schema::hasTable('order_discounts')) {
            Schema::create('order_discounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
                $table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
                $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
                $table->string('code', 80)->nullable();
                $table->string('label');
                $table->string('discount_type', 32);
                $table->decimal('amount', 15, 2)->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('invoice_discounts')) {
            Schema::create('invoice_discounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->cascadeOnDelete();
                $table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
                $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
                $table->string('code', 80)->nullable();
                $table->string('label');
                $table->string('discount_type', 32);
                $table->decimal('amount', 15, 2)->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_discounts');
        Schema::dropIfExists('order_discounts');
        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('promotions');
    }
};
