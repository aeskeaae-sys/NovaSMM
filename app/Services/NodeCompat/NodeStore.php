<?php

namespace App\Services\NodeCompat;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class NodeStore
{
    public static function now(): string { return now()->toIso8601String(); }

    public static function micro(float|int|string $amount): int {
        return (int) round(((float) $amount) * 1_000_000);
    }
    public static function fromMicro(int|float|string|null $micro): float {
        return round(((int) ($micro ?? 0)) / 1_000_000, 2);
    }
    public static function normalizeEmail(string $email): string { return strtolower(trim($email)); }
    public static function makeCode(string $prefix): string { return $prefix . strtoupper(Str::random(10)); }

    public static function siteSettings(): array {
        $defaults = [
            'siteName' => config('app.name', 'NovaSMM'),
            'siteTagline' => 'แพลตฟอร์ม SMM อัตโนมัติ',
            'siteDescription' => 'NovaSMM',
            'announcement' => '',
            'affiliateCommissionPercent' => 5,
            'minAffiliateWithdraw' => 100,
            'welcomeBonusAmount' => 0,
            'depositMethods' => ['bank'=>true,'card'=>false,'crypto'=>false,'paypal'=>false,'perfect_money'=>false],
            'contactFacebook' => '', 'contactTelegram' => '', 'contactLine' => '',
        ];
        if (!\Schema::hasTable('settings_v2')) return $defaults;
        $rows = DB::table('settings_v2')->pluck('value','key');
        foreach ($defaults as $k=>$v) {
            if (!isset($rows[$k])) continue;
            $raw = $rows[$k];
            $decoded = json_decode($raw, true);
            $defaults[$k] = $decoded === null && $raw !== 'null' ? $raw : $decoded;
        }
        return $defaults;
    }

    public static function setSetting(string $key, mixed $value): void {
        DB::table('settings_v2')->updateOrInsert(['key'=>$key], ['value'=>json_encode($value, JSON_UNESCAPED_UNICODE), 'updated_at'=>now()]);
    }

    public static function publicUser(object|array $u): array {
        $u = (array) $u;
        $id = (int) $u['id'];
        $wallet = DB::table('wallets')->where('user_id',$id)->first();
        $aff = DB::table('affiliate_accounts_v2')->where('user_id',$id)->first();
        $api = DB::table('api_keys')->where('user_id',$id)->where('revoked',0)->orderByDesc('id')->first();
        return [
            'id'=>$id,
            'name'=>$u['fullname'] ?? $u['name'] ?? $u['username'],
            'username'=>$u['username'],
            'email'=>$u['email'],
            'role'=>$u['role'] ?? 'user',
            'rank'=>$u['rank'] ?? 'member',
            'status'=>$u['status'] ?? 'active',
            'balance'=>self::fromMicro($wallet->balance_micro ?? 0),
            'totalDeposit'=>self::fromMicro($wallet->total_deposit_micro ?? 0),
            'totalSpent'=>self::fromMicro($wallet->total_spent_micro ?? 0),
            'affiliateBalance'=>self::fromMicro($aff->balance_micro ?? 0),
            'affiliateCode'=>$u['referral_code'] ?? $aff->affiliate_code ?? '',
            'referredBy'=>$u['referral_by'] ?? null,
            'apiKeyPrefix'=>$api->key_prefix ?? '',
            'hasApiKey'=>(bool)$api,
            'currency'=>$u['currency_code'] ?? 'THB',
            'language'=>$u['language'] ?? 'th',
            'createdAt'=>$u['created_at'] ?? null,
            'lastLoginAt'=>$u['last_login_at'] ?? $u['updated_at'] ?? null,
        ];
    }

    public static function ensureWallet(int $userId): void {
        if (!DB::table('wallets')->where('user_id',$userId)->exists()) {
            DB::table('wallets')->insert(['user_id'=>$userId,'balance_micro'=>0,'total_deposit_micro'=>0,'total_spent_micro'=>0,'updated_at'=>now()]);
        }
        if (!DB::table('affiliate_accounts_v2')->where('user_id',$userId)->exists()) {
            $u = DB::table('users')->where('id',$userId)->first();
            DB::table('affiliate_accounts_v2')->insert(['user_id'=>$userId,'affiliate_code'=>$u->referral_code ?? strtoupper(Str::random(8)),'balance_micro'=>0,'total_earned_micro'=>0,'updated_at'=>now()]);
        }
    }

    public static function mapService($r): array {
        $r=(array)$r;
        return [
            'id'=>(int)$r['id'],
            'productCode'=>$r['product_code'] ?: ('PRD-'.str_pad((string)$r['id'],3,'0',STR_PAD_LEFT)),
            'categoryId'=>(int)$r['category_id'],
            'platformId'=>(int)$r['platform_id'],
            'name'=>$r['name'],
            'pricePer1k'=>self::fromMicro($r['price_per_1k_micro']),
            'originalPricePer1k'=>self::fromMicro($r['original_price_per_1k_micro'] ?? 0),
            'minBuy'=>(int)$r['min_buy'],
            'maxBuy'=>(int)$r['max_buy'],
            'type'=>$r['type'],
            'addType'=>$r['add_type'],
            'apiProviderId'=>$r['api_provider_id'] ? (int)$r['api_provider_id'] : null,
            'apiServiceId'=>$r['api_service_id'],
            'refillSupport'=>(bool)$r['refill_support'],
            'cancelSupport'=>(bool)$r['cancel_support'],
            'speedLabel'=>$r['speed_label'],
            'averageTime'=>$r['average_time'],
            'description'=>$r['description'] ?? '',
            'status'=>(bool)$r['status'],
        ];
    }

    public static function mapOrder($r): array {
        $r=(array)$r;
        return [
            'id'=>(int)$r['id'],
            'orderCode'=>$r['order_code'],
            'userId'=>(int)$r['user_id'],
            'username'=>$r['username'],
            'serviceId'=>(int)$r['service_id'],
            'categoryId'=>(int)$r['category_id'],
            'platformId'=>(int)$r['platform_id'],
            'serviceName'=>$r['service_name'],
            'objectId'=>$r['object_id'],
            'quantity'=>(int)$r['quantity'],
            'startNumber'=>(int)$r['start_number'],
            'remains'=>(int)$r['remains'],
            'successCount'=>(int)$r['success_count'],
            'pricePer1k'=>self::fromMicro($r['price_per_1k_micro']),
            'discountPercent'=>(int)$r['discount_percent'],
            'totalPayment'=>self::fromMicro($r['total_payment_micro']),
            'providerCost'=>self::fromMicro($r['provider_cost_micro']),
            'providerId'=>$r['provider_id'] ? (int)$r['provider_id'] : null,
            'providerOrderId'=>$r['provider_order_id'],
            'providerError'=>$r['provider_error'],
            'status'=>$r['status'],
            'orderNote'=>$r['order_note'] ?? '',
            'comments'=>json_decode($r['comments_json'] ?? '[]', true) ?: [],
            'reactionType'=>$r['reaction_type'],
            'refillRequested'=>(bool)$r['refill_requested'],
            'refillStatus'=>$r['refill_status'] ?? 'none',
            'refundedAmount'=>self::fromMicro($r['refunded_micro']),
            'createdAt'=>$r['created_at'],
            'updatedAt'=>$r['updated_at'],
        ];
    }

    public static function mapTransaction($r): array {
        $r=(array)$r;
        return [
            'id'=>(int)$r['id'],
            'code'=>$r['code'],
            'userId'=>(int)$r['user_id'],
            'username'=>$r['username'],
            'type'=>$r['type'],
            'amount'=>self::fromMicro($r['amount_micro']),
            'balanceBefore'=>self::fromMicro($r['balance_before_micro']),
            'balanceAfter'=>self::fromMicro($r['balance_after_micro']),
            'status'=>$r['status'],
            'content'=>$r['content'],
            'idempotencyKey'=>$r['idempotency_key'] ?? null,
            'createdAt'=>$r['created_at'],
        ];
    }

    public static function audit(?int $actorId, string $actorUsername, string $action, string $targetType, string $targetId, string $reason='', array $meta=[], string $ip=''): void {
        if (!\Schema::hasTable('audit_logs_v2')) return;
        DB::table('audit_logs_v2')->insert([
            'actor_user_id'=>$actorId,'actor_username'=>$actorUsername,'action'=>$action,'target_type'=>$targetType,
            'target_id'=>$targetId,'reason'=>$reason,'metadata'=>json_encode($meta, JSON_UNESCAPED_UNICODE),'ip_address'=>$ip,'created_at'=>now(),
        ]);
    }
}

