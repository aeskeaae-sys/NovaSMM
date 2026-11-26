<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Node-compatible schema (NovaSMM v2). Keep additive; existing Laravel tables remain.
        if (!Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->primary();
                $table->text('bio')->default('');
                $table->string('phone', 64)->default('');
                $table->string('telegram', 64)->default('');
                $table->timestamp('updated_at')->nullable();
            });
        }
        if (!Schema::hasTable('wallets')) {
            Schema::create('wallets', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->primary();
                $table->bigInteger('balance_micro')->default(0);
                $table->bigInteger('total_deposit_micro')->default(0);
                $table->bigInteger('total_spent_micro')->default(0);
                $table->timestamp('updated_at')->nullable();
            });
        }
        if (!Schema::hasTable('api_keys')) {
            Schema::create('api_keys', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('key_hash', 128)->unique();
                $table->string('key_prefix', 32);
                $table->boolean('revoked')->default(false);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('last_used_at')->nullable();
            });
        }
        if (!Schema::hasTable('password_reset_tokens_v2')) {
            Schema::create('password_reset_tokens_v2', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('token_hash', 128)->unique();
                $table->timestamp('expires_at');
                $table->timestamp('used_at')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
        if (!Schema::hasTable('platforms_v2')) {
            Schema::create('platforms_v2', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('code', 16);
                $table->string('badge_color', 32);
                $table->boolean('status')->default(true);
                $table->integer('priority')->default(1);
            });
        }
        if (!Schema::hasTable('categories_v2')) {
            Schema::create('categories_v2', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('platform_id')->index();
                $table->string('name');
                $table->string('slug')->unique();
                $table->boolean('status')->default(true);
                $table->integer('priority')->default(1);
            });
        }
        if (!Schema::hasTable('providers_v2')) {
            Schema::create('providers_v2', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('url');
                $table->text('api_key_encrypted');
                $table->string('api_key_masked', 64);
                $table->bigInteger('balance_micro')->default(0);
                $table->string('currency', 8)->default('THB');
                $table->integer('price_increase_percent')->default(30);
                $table->boolean('auto_sync')->default(true);
                $table->boolean('status')->default(true);
                $table->timestamp('last_sync_at')->nullable();
                $table->text('last_error')->nullable();
            });
        }
        if (!Schema::hasTable('provider_services_v2')) {
            Schema::create('provider_services_v2', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('provider_id')->index();
                $table->string('external_service_id', 64);
                $table->string('name');
                $table->string('category_name');
                $table->bigInteger('rate_micro');
                $table->integer('min_buy');
                $table->integer('max_buy');
                $table->boolean('refill_support')->default(false);
                $table->boolean('cancel_support')->default(false);
                $table->timestamp('synced_at')->nullable();
                $table->unique(['provider_id','external_service_id'], 'provider_services_v2_unique');
            });
        }
        if (!Schema::hasTable('provider_logs_v2')) {
            Schema::create('provider_logs_v2', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('provider_id')->index();
                $table->string('provider_name');
                $table->string('action', 64);
                $table->unsignedBigInteger('order_id')->nullable();
                $table->integer('http_status')->nullable();
                $table->boolean('success')->default(false);
                $table->text('message');
                $table->timestamp('created_at')->nullable();
            });
        }
        if (!Schema::hasTable('services_v2')) {
            Schema::create('services_v2', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id')->index();
                $table->unsignedBigInteger('platform_id')->index();
                $table->string('product_code', 32)->default('');
                $table->string('name');
                $table->bigInteger('price_per_1k_micro');
                $table->bigInteger('original_price_per_1k_micro')->default(0);
                $table->integer('min_buy')->default(100);
                $table->integer('max_buy')->default(100000);
                $table->string('type', 32)->default('default');
                $table->string('add_type', 16)->default('manual');
                $table->unsignedBigInteger('api_provider_id')->nullable()->index();
                $table->string('api_service_id', 64)->nullable();
                $table->boolean('refill_support')->default(false);
                $table->boolean('cancel_support')->default(false);
                $table->string('speed_label', 64)->default('5K / วัน');
                $table->string('average_time', 64)->default('15 นาที');
                $table->text('description')->nullable();
                $table->boolean('is_demo')->default(false);
                $table->boolean('status')->default(true);
                $table->unique(['product_code'], 'services_v2_product_code_unique');
            });
        }
        if (!Schema::hasTable('orders_v2')) {
            Schema::create('orders_v2', function (Blueprint $table) {
                $table->id();
                $table->string('order_code', 64)->unique();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('username');
                $table->unsignedBigInteger('service_id')->index();
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('platform_id');
                $table->string('service_name');
                $table->text('object_id');
                $table->integer('quantity');
                $table->integer('start_number')->default(0);
                $table->integer('remains')->default(0);
                $table->integer('success_count')->default(0);
                $table->bigInteger('price_per_1k_micro');
                $table->integer('discount_percent')->default(0);
                $table->bigInteger('total_payment_micro');
                $table->bigInteger('provider_cost_micro')->default(0);
                $table->unsignedBigInteger('provider_id')->nullable();
                $table->string('provider_order_id', 64)->nullable();
                $table->text('provider_error')->nullable();
                $table->string('status', 32)->default('Pending')->index();
                $table->text('order_note')->nullable();
                $table->json('comments_json')->nullable();
                $table->string('reaction_type', 64)->nullable();
                $table->boolean('refill_requested')->default(false);
                $table->string('refill_status', 32)->default('none');
                $table->bigInteger('refunded_micro')->default(0);
                $table->string('idempotency_key', 128)->nullable()->unique();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }
        if (!Schema::hasTable('transactions_v2')) {
            Schema::create('transactions_v2', function (Blueprint $table) {
                $table->id();
                $table->string('code', 64);
                $table->unsignedBigInteger('user_id')->index();
                $table->string('username');
                $table->string('type', 32)->index();
                $table->bigInteger('amount_micro');
                $table->bigInteger('balance_before_micro');
                $table->bigInteger('balance_after_micro');
                $table->string('status', 16)->default('paid');
                $table->text('content');
                $table->string('idempotency_key', 128)->nullable()->unique();
                $table->timestamp('created_at')->nullable();
            });
        }
        if (!Schema::hasTable('bank_accounts_v2')) {
            Schema::create('bank_accounts_v2', function (Blueprint $table) {
                $table->id();
                $table->string('bank_name');
                $table->string('short_name', 32);
                $table->string('account_name');
                $table->string('account_number', 64);
                $table->string('prompt_pay_id', 64)->default('');
                $table->bigInteger('min_deposit_micro')->default(200000);
                $table->boolean('status')->default(true);
            });
        }
        if (!Schema::hasTable('deposit_invoices_v2')) {
            Schema::create('deposit_invoices_v2', function (Blueprint $table) {
                $table->id();
                $table->string('code', 64)->unique();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('username');
                $table->string('method', 32);
                $table->bigInteger('amount_micro');
                $table->bigInteger('fee_micro')->default(0);
                $table->bigInteger('net_amount_micro');
                $table->string('channel_name');
                $table->string('reference', 128);
                $table->text('proof_note')->nullable();
                $table->string('status', 16)->default('pending')->index();
                $table->string('reviewed_by')->nullable();
                $table->string('idempotency_key', 128)->nullable()->unique();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('paid_at')->nullable();
            });
        }
        if (!Schema::hasTable('vouchers_v2')) {
            Schema::create('vouchers_v2', function (Blueprint $table) {
                $table->id();
                $table->string('code', 64)->unique();
                $table->bigInteger('value_micro');
                $table->integer('max_uses')->default(100);
                $table->integer('used_count')->default(0);
                $table->json('allowed_ranks_json')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('status')->default(true);
            });
        }
        if (!Schema::hasTable('voucher_redemptions_v2')) {
            Schema::create('voucher_redemptions_v2', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('voucher_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('username');
                $table->bigInteger('value_micro');
                $table->timestamp('redeemed_at')->nullable();
                $table->unique(['voucher_id','user_id']);
            });
        }
        if (!Schema::hasTable('affiliate_accounts_v2')) {
            Schema::create('affiliate_accounts_v2', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->primary();
                $table->string('affiliate_code', 32)->unique();
                $table->bigInteger('balance_micro')->default(0);
                $table->bigInteger('total_earned_micro')->default(0);
                $table->timestamp('updated_at')->nullable();
            });
        }
        if (!Schema::hasTable('affiliate_transactions_v2')) {
            Schema::create('affiliate_transactions_v2', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('referrer_user_id')->index();
                $table->unsignedBigInteger('referred_user_id')->nullable();
                $table->string('type', 32);
                $table->bigInteger('amount_micro');
                $table->text('note');
                $table->timestamp('created_at')->nullable();
            });
        }
        if (!Schema::hasTable('notifications_v2')) {
            Schema::create('notifications_v2', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('title');
                $table->text('message');
                $table->string('type', 32)->default('system');
                $table->boolean('read')->default(false);
                $table->timestamp('created_at')->nullable();
            });
        }
        if (!Schema::hasTable('announcements_v2')) {
            Schema::create('announcements_v2', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->string('level', 16)->default('promo');
                $table->boolean('active')->default(true);
                $table->timestamp('created_at')->nullable();
            });
        }
        if (!Schema::hasTable('settings_v2')) {
            Schema::create('settings_v2', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->text('value');
                $table->timestamp('updated_at')->nullable();
            });
        }
        if (!Schema::hasTable('posts_v2')) {
            Schema::create('posts_v2', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('title');
                $table->text('excerpt');
                $table->longText('content');
                $table->string('category', 64);
                $table->boolean('status')->default(true);
                $table->timestamp('created_at')->nullable();
            });
        }
        if (!Schema::hasTable('audit_logs_v2')) {
            Schema::create('audit_logs_v2', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('actor_user_id')->nullable()->index();
                $table->string('actor_username');
                $table->string('action', 64);
                $table->string('target_type', 64);
                $table->string('target_id', 64);
                $table->text('reason')->nullable();
                $table->json('metadata')->nullable();
                $table->string('ip_address', 64)->default('');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive: keep v2 tables.
    }
};
