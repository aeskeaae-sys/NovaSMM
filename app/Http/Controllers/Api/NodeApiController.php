<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use App\Services\NodeCompat\NodeStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class NodeApiController extends Controller
{
    private function u(Request $r): ?User { return $r->user(); }
    private function requireUser(Request $r): User { $u=$this->u($r); abort_unless($u,401,'Unauthorized'); return $u; }
    private function requireAdmin(Request $r): User { $u=$this->requireUser($r); abort_unless(($u->role??'')==='admin',403,'Forbidden'); return $u; }

    private function hasV2(): bool { return Schema::hasTable('services_v2'); }

    public function health() {
        return response()->json(['ok'=>true,'service'=>'novasmm-api','uptimeSeconds'=>0,'db'=>['ok'=>true],'timestamp'=>now()->toIso8601String()]);
    }

    public function bootstrap(Request $r) {
        $s = NodeStore::siteSettings();
        $user = $this->u($r);
        $needsAdminSetup = !User::where('role','admin')->exists();
        $publicStats = ['totalUsers'=>User::count(),'totalServices'=>$this->hasV2()?DB::table('services_v2')->where('status',1)->count():DB::table('services')->count(),'totalOrders'=>$this->hasV2()?DB::table('orders_v2')->count():DB::table('orders')->count()];
        $base = [
            'siteSettings'=>[
                'siteName'=>$s['siteName'],'siteTagline'=>$s['siteTagline'],'siteDescription'=>$s['siteDescription'],
                'contactFacebook'=>$s['contactFacebook'],'contactTelegram'=>$s['contactTelegram'],'contactLine'=>$s['contactLine'],
            ],
            'needsAdminSetup'=>$needsAdminSetup,
            'publicStats'=>$publicStats,
        ];
        if (!$user) return response()->json($base);
        NodeStore::ensureWallet($user->id);
        $dbUser = NodeStore::publicUser($user);
        $platforms = $this->hasV2() ? DB::table('platforms_v2')->where('status',1)->orderByDesc('priority')->get()->map(fn($p)=>[
            'id'=>(int)$p->id,'name'=>$p->name,'slug'=>$p->slug,'code'=>$p->code,'badgeColor'=>$p->badge_color,'status'=>(bool)$p->status,'priority'=>(int)$p->priority
        ])->values() : collect();
        $categories = $this->hasV2() ? DB::table('categories_v2')->where('status',1)->orderByDesc('priority')->get()->map(fn($c)=>[
            'id'=>(int)$c->id,'platformId'=>(int)$c->platform_id,'name'=>$c->name,'slug'=>$c->slug,'status'=>(bool)$c->status,'priority'=>(int)$c->priority
        ])->values() : collect();
        $services = $this->hasV2() ? DB::table('services_v2')->where('status',1)->orderBy('id')->get()->map(fn($x)=>NodeStore::mapService($x))->values() : collect();
        $ann = $this->hasV2() ? DB::table('announcements_v2')->where('active',1)->orderByDesc('id')->limit(10)->get()->map(fn($a)=>['id'=>(int)$a->id,'title'=>$a->title,'content'=>$a->content,'level'=>$a->level,'active'=>(bool)$a->active,'createdAt'=>$a->created_at])->values() : collect();
        $notifs = $this->hasV2() ? DB::table('notifications_v2')->where('user_id',$user->id)->orderByDesc('id')->limit(20)->get()->map(fn($n)=>['id'=>(int)$n->id,'userId'=>(int)$n->user_id,'title'=>$n->title,'message'=>$n->message,'type'=>$n->type,'read'=>(bool)$n->read,'createdAt'=>$n->created_at])->values() : collect();
        return response()->json(array_merge($base, [
            'user'=>$dbUser,'platforms'=>$platforms,'categories'=>$categories,'services'=>$services,
            'announcements'=>$ann,'notifications'=>$notifs,'siteSettings'=>array_merge($base['siteSettings'], $s),
            'rankTiers'=>[
                ['rank'=>'member','label'=>'Member','discountPercent'=>0,'minSpentThreshold'=>0],
                ['rank'=>'silver','label'=>'Silver','discountPercent'=>1,'minSpentThreshold'=>1000],
                ['rank'=>'gold','label'=>'Gold','discountPercent'=>2,'minSpentThreshold'=>5000],
                ['rank'=>'platinum','label'=>'Platinum','discountPercent'=>4,'minSpentThreshold'=>20000],
                ['rank'=>'agency','label'=>'Agency','discountPercent'=>6,'minSpentThreshold'=>50000],
            ],
        ]));
    }

    public function setupAdmin(Request $r) {
        abort_if(User::where('role','admin')->exists(), 409, 'Admin already exists');
        $data = $r->validate(['name'=>'required','username'=>'required','email'=>'required|email','password'=>'required|min:8','siteName'=>'nullable','siteTagline'=>'nullable']);
        $user = User::create([
            'fullname'=>$data['name'],'username'=>$data['username'],'email'=>NodeStore::normalizeEmail($data['email']),
            'password'=>Hash::make($data['password']),'role'=>'admin','status'=>'active','rank'=>'member',
            'currency_code'=>'THB','language'=>'th','referral_code'=>strtoupper(Str::random(8)),
            'ip_address'=>$r->ip(),'last_login_at'=>now(),'last_login_ip'=>$r->ip(),
        ]);
        NodeStore::ensureWallet($user->id);
        if (!empty($data['siteName'])) NodeStore::setSetting('siteName',$data['siteName']);
        if (!empty($data['siteTagline'])) NodeStore::setSetting('siteTagline',$data['siteTagline']);
        Auth::login($user);
        $r->session()->regenerate();
        return response()->json(['user'=>NodeStore::publicUser($user)], 201);
    }

    public function register(Request $r) {
        $data = $r->validate(['name'=>'required','username'=>'required','email'=>'required|email','password'=>'required|min:8','ref'=>'nullable']);
        abort_if(User::where('email',NodeStore::normalizeEmail($data['email']))->exists(), 409, 'Email already used');
        abort_if(User::where('username',$data['username'])->exists(), 409, 'Username already used');
        $user = User::create([
            'fullname'=>$data['name'],'username'=>$data['username'],'email'=>NodeStore::normalizeEmail($data['email']),
            'password'=>Hash::make($data['password']),'role'=>'user','status'=>'active','rank'=>'member',
            'currency_code'=>'THB','language'=>'th','referral_code'=>strtoupper(Str::random(8)),
            'referral_by'=>$data['ref'] ?? null,'ip_address'=>$r->ip(),'last_login_at'=>now(),'last_login_ip'=>$r->ip(),
        ]);
        NodeStore::ensureWallet($user->id);
        Auth::login($user); $r->session()->regenerate();
        return response()->json(['user'=>NodeStore::publicUser($user)], 201);
    }

    public function login(Request $r) {
        $data = $r->validate(['identifier'=>'required','password'=>'required']);
        $id = $data['identifier'];
        $user = User::where('email',NodeStore::normalizeEmail($id))->orWhere('username',$id)->first();
        abort_unless($user && Hash::check($data['password'], $user->password), 401, 'Invalid credentials');
        abort_if(($user->status ?? 'active') === 'locked', 403, 'Account locked');
        $user->forceFill(['last_login_at'=>now(),'last_login_ip'=>$r->ip()])->save();
        NodeStore::ensureWallet($user->id);
        Auth::login($user); $r->session()->regenerate();
        return response()->json(['user'=>NodeStore::publicUser($user)]);
    }

    public function logout(Request $r) { Auth::logout(); $r->session()->invalidate(); $r->session()->regenerateToken(); return response()->json(['ok'=>true]); }
    public function me(Request $r) { $u=$this->u($r); return response()->json(['user'=>$u?NodeStore::publicUser($u):null]); }
    public function forgotPassword(Request $r) { $r->validate(['email'=>'required|email']); return response()->json(['ok'=>true,'message'=>'If email exists, reset instructions sent.']); }
    public function resetPassword(Request $r) { $r->validate(['token'=>'required','newPassword'=>'required|min:8']); return response()->json(['message'=>'รีเซ็ตรหัสผ่านสำเร็จ']); }

    public function listServices() {
        if (!$this->hasV2()) return response()->json(['data'=>[],'status'=>200,'message'=>'OK']);
        $rows = DB::table('services_v2 as s')->join('categories_v2 as c','c.id','=','s.category_id')->where('s.status',1)->orderBy('s.id')->get(['s.*','c.name as category_name']);
        $data = $rows->map(function($r){ $s=NodeStore::mapService($r); $price=$s['pricePer1k']/1000; return [
            'id'=>$s['id'],'category_id'=>$s['categoryId'],'name'=>$s['name'],'display_name'=>"[{$s['productCode']}] {$s['name']} — ".number_format($s['pricePer1k'],2),
            'price_per'=>$price,'price'=>$price,'price_formatted'=>number_format($price,2),'min_buy'=>$s['minBuy'],'max_buy'=>$s['maxBuy'],
            'type'=>$s['type']==='custom_comments'?'custom_comments':$s['type'],'refill'=>$s['refillSupport'],'cancel'=>$s['cancelSupport'],'status'=>$s['status'],
            'image'=>null,'descr'=>$s['description'],'average_time'=>$s['averageTime'],'text'=>$s['name'],'category_name'=>$r->category_name,
        ]; })->values();
        return response()->json(['data'=>$data,'status'=>200,'message'=>'Get list service success']);
    }

    public function presetPlatforms() {
        if (!$this->hasV2()) return response()->json(['data'=>[],'status'=>200,'message'=>'OK']);
        $data = DB::table('platforms_v2')->where('status',1)->orderByDesc('priority')->get()->map(fn($p)=>['id'=>(int)$p->id,'name'=>$p->name,'code'=>$p->code,'status'=>(bool)$p->status,'image'=>null])->values();
        return response()->json(['data'=>$data,'status'=>200,'message'=>'Get list platform success']);
    }
    public function presetCategories(Request $r) {
        if (!$this->hasV2()) return response()->json(['data'=>[],'status'=>200,'message'=>'OK']);
        $q = DB::table('categories_v2')->where('status',1);
        if ($r->integer('platform_id')>0) $q->where('platform_id',$r->integer('platform_id'));
        $rows = $q->orderByDesc('priority')->get();
        if ($rows->isEmpty()) return response()->json(['status'=>400,'message'=>'ไม่พบหมวดหมู่'],400);
        return response()->json(['data'=>$rows->map(fn($c)=>['id'=>(int)$c->id,'platform_id'=>(int)$c->platform_id,'name'=>$c->name,'status'=>(bool)$c->status,'image'=>null])->values(),'status'=>200,'message'=>'Get list category success']);
    }
    public function presetServiceInfo(Request $r) {
        $id=$r->integer('service_id'); abort_unless($id>0,400,'service_id required');
        $row = DB::table('services_v2')->where('id',$id)->first();
        abort_unless($row,400,'ไม่พบบริการ');
        $s=NodeStore::mapService($row);
        return response()->json(['data'=>['id'=>$s['id'],'name'=>$s['name'],'price_per'=>$s['pricePer1k']/1000,'min_buy'=>$s['minBuy'],'max_buy'=>$s['maxBuy'],'type'=>$s['type'],'refill'=>$s['refillSupport'],'cancel'=>$s['cancelSupport'],'average_time'=>$s['averageTime'],'descr'=>$s['description'],'status'=>$s['status']],'status'=>200,'message'=>'Get service info success']);
    }

    public function createOrder(Request $r) {
        $u=$this->requireUser($r);
        $serviceId=(int)($r->input('serviceId') ?? $r->input('service_id'));
        $quantity=(int)($r->input('quantity'));
        $objectId=trim((string)($r->input('objectId') ?? $r->input('object_id') ?? $r->input('link')));
        abort_unless($serviceId>0 && $quantity>0 && $objectId!=='', 422, 'Invalid order');
        $svc = DB::table('services_v2')->where('id',$serviceId)->where('status',1)->first();
        abort_unless($svc,404,'Service not found');
        $s=NodeStore::mapService($svc);
        abort_if($quantity < $s['minBuy'] || $quantity > $s['maxBuy'], 422, 'Quantity out of range');
        NodeStore::ensureWallet($u->id);
        $total = round(($s['pricePer1k']/1000)*$quantity,2);
        $isAdmin = ($u->role==='admin');
        $charge = $isAdmin ? 0 : $total;
        $orderCode = NodeStore::makeCode('ORD');
        $now=now();
        $orderId = DB::transaction(function() use($u,$s,$quantity,$objectId,$orderCode,$charge,$total,$now,$r){
            $wallet = DB::table('wallets')->where('user_id',$u->id)->lockForUpdate()->first();
            $before=(int)($wallet->balance_micro ?? 0);
            abort_if(NodeStore::fromMicro($before) < $charge, 422, 'ยอดเงินไม่พอ');
            $after=$before - NodeStore::micro($charge);
            DB::table('wallets')->where('user_id',$u->id)->update(['balance_micro'=>$after,'total_spent_micro'=>DB::raw('total_spent_micro+'.NodeStore::micro($total)),'updated_at'=>$now]);
            $oid = DB::table('orders_v2')->insertGetId([
                'order_code'=>$orderCode,'user_id'=>$u->id,'username'=>$u->username,'service_id'=>$s['id'],'category_id'=>$s['categoryId'],'platform_id'=>$s['platformId'],
                'service_name'=>$s['name'],'object_id'=>$objectId,'quantity'=>$quantity,'start_number'=>0,'remains'=>$quantity,'success_count'=>0,
                'price_per_1k_micro'=>NodeStore::micro($s['pricePer1k']),'discount_percent'=>0,'total_payment_micro'=>NodeStore::micro($total),
                'provider_cost_micro'=>0,'provider_id'=>$s['apiProviderId'],'provider_order_id'=>null,'provider_error'=>null,'status'=>'Pending',
                'order_note'=>(string)$r->input('orderNote',$r->input('order_note','')),'comments_json'=>json_encode([]),'reaction_type'=>$r->input('reactionType'),
                'refill_requested'=>0,'refill_status'=>'none','refunded_micro'=>0,'idempotency_key'=>$r->header('X-Idempotency-Key') ?: $r->input('idempotencyKey'),
                'created_at'=>$now,'updated_at'=>$now,
            ]);
            DB::table('transactions_v2')->insert(['code'=>NodeStore::makeCode('TRX'),'user_id'=>$u->id,'username'=>$u->username,'type'=>'new-order','amount_micro'=>-NodeStore::micro($charge),'balance_before_micro'=>$before,'balance_after_micro'=>$after,'status'=>'paid','content'=>"Order #{$oid} {$s['name']}",'created_at'=>$now]);
            return $oid;
        });
        $order = NodeStore::mapOrder(DB::table('orders_v2')->where('id',$orderId)->first());
        $fresh = NodeStore::publicUser(User::find($u->id));
        return response()->json(['message'=>"สร้างคำสั่งซื้อสำเร็จ 1 รายการ",'orders'=>[$order],'user'=>$fresh,'status'=>201,'data'=>['transaction'=>['code'=>$orderCode,'amount'=>$total],'order_detail'=>['id'=>$orderId,'code'=>$orderCode]]],201);
    }

    public function listOrders(Request $r) {
        $u=$this->requireUser($r);
        $q=DB::table('orders_v2')->where('user_id',$u->id)->orderByDesc('id');
        if ($r->filled('status') && $r->string('status')!=='ALL') $q->where('status',$r->string('status'));
        if ($r->filled('search')) { $s='%'.strtolower($r->string('search')).'%'; $q->where(function($w) use($s){$w->whereRaw('lower(order_code) like ?',[$s])->orWhereRaw('lower(service_name) like ?',[$s])->orWhereRaw('lower(object_id) like ?',[$s]);}); }
        $rows=$q->limit(min(200,(int)$r->integer('limit',50)))->offset((int)$r->integer('offset',0))->get();
        return response()->json(['orders'=>$rows->map(fn($x)=>NodeStore::mapOrder($x))->values()]);
    }
}
    public function meUser(Request $r){ $u=$this->requireUser($r); $p=NodeStore::publicUser($u); $p['balance_formatted']=number_format($p['balance'],2); return response()->json($p); }
    public function profileUpdate(Request $r){ $u=$this->requireUser($r); $u->forceFill(['fullname'=>$r->input('name',$u->fullname),'language'=>$r->input('language',$u->language),'currency_code'=>$r->input('currency',$u->currency_code)])->save(); return response()->json(['message'=>'บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว','user'=>NodeStore::publicUser($u->fresh())]); }
    public function profilePassword(Request $r){ $u=$this->requireUser($r); abort_unless(Hash::check((string)$r->input('currentPassword'),$u->password),422,'รหัสผ่านปัจจุบันไม่ถูกต้อง'); $u->forceFill(['password'=>Hash::make((string)$r->input('newPassword'))])->save(); return response()->json(['message'=>'เปลี่ยนรหัสผ่านสำเร็จ']); }
    public function profileToken(Request $r){ $u=$this->requireUser($r); DB::table('api_keys')->where('user_id',$u->id)->update(['revoked'=>1]); $raw='nv_live_'.Str::random(40); DB::table('api_keys')->insert(['user_id'=>$u->id,'key_hash'=>hash('sha256',$raw),'key_prefix'=>substr($raw,0,12).'...'.substr($raw,-4),'revoked'=>0,'created_at'=>now(),'last_used_at'=>null]); return response()->json(['message'=>'สร้าง API Key ใหม่เรียบร้อยแล้ว','user'=>NodeStore::publicUser($u->fresh()),'rawApiKey'=>$raw]); }
    public function profileTokenRevoke(Request $r){ $u=$this->requireUser($r); DB::table('api_keys')->where('user_id',$u->id)->update(['revoked'=>1]); return response()->json(['message'=>'เพิกถอน API Key แล้ว','user'=>NodeStore::publicUser($u->fresh())]); }

    public function getOrderById(Request $r){ $u=$this->requireUser($r); $id=(int)$r->query('id'); $q=DB::table('orders_v2')->where('id',$id); if($u->role!=='admin') $q->where('user_id',$u->id); $row=$q->first(); abort_unless($row,404,'Order not found'); return response()->json(['data'=>NodeStore::mapOrder($row),'status'=>200,'message'=>'OK']); }
    public function getOrdersByIds(Request $r){ $u=$this->requireUser($r); $ids=collect(explode(',',(string)$r->query('ids')))->filter()->map(fn($x)=>(int)$x)->take(200)->values(); $rows=DB::table('orders_v2')->where('user_id',$u->id)->whereIn('id',$ids)->orderByDesc('id')->get(); return response()->json(['data'=>$rows->map(fn($x)=>NodeStore::mapOrder($x))->values(),'status'=>200,'message'=>'OK']); }
    public function syncOrder(Request $r,$id){ $u=$this->requireUser($r); $row=DB::table('orders_v2')->where('id',(int)$id)->where('user_id',$u->id)->first(); abort_unless($row,404,'Not found'); return response()->json(['message'=>'ซิงค์สถานะสำเร็จ','order'=>NodeStore::mapOrder($row)]); }
    public function refillOrder(Request $r,$id){ $u=$this->requireUser($r); DB::table('orders_v2')->where('id',(int)$id)->where('user_id',$u->id)->update(['refill_requested'=>1,'refill_status'=>'pending','updated_at'=>now()]); $row=DB::table('orders_v2')->where('id',(int)$id)->first(); return response()->json(['message'=>'รับคำขอ Refill แล้ว','order'=>NodeStore::mapOrder($row)]); }
    public function cancelOrder(Request $r,$id){ $u=$this->requireUser($r); $row=DB::table('orders_v2')->where('id',(int)$id)->where('user_id',$u->id)->first(); abort_unless($row,404,'Not found'); if(in_array($row->status,['Pending','Processing'])){ DB::table('orders_v2')->where('id',$row->id)->update(['status'=>'Canceled','updated_at'=>now()]); } $row=DB::table('orders_v2')->where('id',(int)$id)->first(); return response()->json(['message'=>'ยกเลิกคำสั่งซื้อแล้ว','order'=>NodeStore::mapOrder($row),'user'=>NodeStore::publicUser($u->fresh())]); }
    public function orderAction(Request $r,$id,$action){ if($action==='sync'||$action==='update') return $this->syncOrder($r,$id); if($action==='refill') return $this->refillOrder($r,$id); if($action==='cancel') return $this->cancelOrder($r,$id); abort(400,'Unsupported action'); }

    public function deposits(Request $r){ $u=$this->requireUser($r); $banks=DB::table('bank_accounts_v2')->where('status',1)->get()->map(fn($b)=>['id'=>(int)$b->id,'bankName'=>$b->bank_name,'shortName'=>$b->short_name,'accountName'=>$b->account_name,'accountNumber'=>$b->account_number,'promptPayId'=>$b->prompt_pay_id,'minDeposit'=>NodeStore::fromMicro($b->min_deposit_micro),'status'=>(bool)$b->status])->values(); $invoices=DB::table('deposit_invoices_v2')->where('user_id',$u->id)->orderByDesc('id')->limit(100)->get()->map(fn($i)=>['id'=>(int)$i->id,'code'=>$i->code,'userId'=>(int)$i->user_id,'username'=>$i->username,'method'=>$i->method,'amount'=>NodeStore::fromMicro($i->amount_micro),'fee'=>NodeStore::fromMicro($i->fee_micro),'netAmount'=>NodeStore::fromMicro($i->net_amount_micro),'channelName'=>$i->channel_name,'reference'=>$i->reference,'proofNote'=>$i->proof_note,'status'=>$i->status,'reviewedBy'=>$i->reviewed_by,'createdAt'=>$i->created_at,'paidAt'=>$i->paid_at])->values(); return response()->json(['banks'=>$banks,'invoices'=>$invoices]); }
    public function banks(Request $r){ return response()->json(['data'=>$this->deposits($r)->getData(true)['banks'],'status'=>200,'message'=>'OK']); }
    public function createInvoice(Request $r){ $u=$this->requireUser($r); $amount=(float)$r->input('amount',0); abort_if($amount<=0,422,'Invalid amount'); $code=NodeStore::makeCode('INV'); $id=DB::table('deposit_invoices_v2')->insertGetId(['code'=>$code,'user_id'=>$u->id,'username'=>$u->username,'method'=>$r->input('method','bank'),'amount_micro'=>NodeStore::micro($amount),'fee_micro'=>0,'net_amount_micro'=>NodeStore::micro($amount),'channel_name'=>$r->input('channelName','Bank'),'reference'=>$r->input('reference',''),'proof_note'=>$r->input('proofNote',$r->input('note','')),'status'=>'pending','reviewed_by'=>null,'idempotency_key'=>$r->header('X-Idempotency-Key') ?: $r->input('idempotencyKey'),'created_at'=>now(),'paid_at'=>null]); $inv=DB::table('deposit_invoices_v2')->where('id',$id)->first(); $invoice=['id'=>$id,'code'=>$code,'status'=>'pending','amount'=>$amount]; return response()->json(['message'=>"สร้างใบแจ้งหนี้ $code สำเร็จ",'invoice'=>$invoice,'data'=>$invoice,'status'=>201,'user'=>NodeStore::publicUser($u->fresh())],201); }
    public function userInvoices(Request $r){ $d=$this->deposits($r)->getData(true); return response()->json(['data'=>$d['invoices'],'status'=>200,'message'=>'OK']); }
    public function userInvoiceShow(Request $r,$id){ $u=$this->requireUser($r); $inv=collect($this->deposits($r)->getData(true)['invoices'])->firstWhere('id',(int)$id); abort_unless($inv,404,'Invoice not found'); return response()->json(['data'=>$inv,'status'=>200,'message'=>'OK']); }
    public function depositWebhook(Request $r){ return response()->json(['ok'=>true]); }
    public function redeemVoucher(Request $r){ $u=$this->requireUser($r); $code=trim((string)$r->input('code')); $v=DB::table('vouchers_v2')->whereRaw('upper(code)=?', [strtoupper($code)])->where('status',1)->first(); abort_unless($v,404,'Voucher not found'); abort_if($v->used_count >= $v->max_uses,422,'Voucher used up'); $exists=DB::table('voucher_redemptions_v2')->where('voucher_id',$v->id)->where('user_id',$u->id)->exists(); abort_if($exists,422,'Already redeemed'); DB::transaction(function() use($u,$v){ DB::table('voucher_redemptions_v2')->insert(['voucher_id'=>$v->id,'user_id'=>$u->id,'username'=>$u->username,'value_micro'=>$v->value_micro,'redeemed_at'=>now()]); DB::table('vouchers_v2')->where('id',$v->id)->increment('used_count'); NodeStore::ensureWallet($u->id); $w=DB::table('wallets')->where('user_id',$u->id)->first(); $before=(int)$w->balance_micro; $after=$before+(int)$v->value_micro; DB::table('wallets')->where('user_id',$u->id)->update(['balance_micro'=>$after,'updated_at'=>now()]); DB::table('transactions_v2')->insert(['code'=>NodeStore::makeCode('TRX'),'user_id'=>$u->id,'username'=>$u->username,'type'=>'gift-code','amount_micro'=>(int)$v->value_micro,'balance_before_micro'=>$before,'balance_after_micro'=>$after,'status'=>'paid','content'=>"Redeem {$v->code}",'created_at'=>now()]); }); return response()->json(['message'=>'Redeemed','status'=>200,'data'=>['code'=>$v->code,'value'=>NodeStore::fromMicro($v->value_micro)],'user'=>NodeStore::publicUser($u->fresh())]); }

    public function transactions(Request $r){ $u=$this->requireUser($r); $q=DB::table('transactions_v2')->where('user_id',$u->id)->orderByDesc('id'); if($r->filled('type') && $r->string('type')!=='ALL') $q->where('type',$r->string('type')); $rows=$q->limit(200)->get(); return response()->json(['transactions'=>$rows->map(fn($x)=>NodeStore::mapTransaction($x))->values(),'data'=>$rows->map(fn($x)=>NodeStore::mapTransaction($x))->values(),'status'=>200,'message'=>'OK']); }
    public function userHistories(Request $r){ $u=$this->requireUser($r); $rows=DB::table('audit_logs_v2')->where('actor_user_id',$u->id)->orderByDesc('id')->limit(200)->get(); return response()->json(['data'=>$rows,'status'=>200,'message'=>'OK']); }
    public function affiliates(Request $r){ $u=$this->requireUser($r); NodeStore::ensureWallet($u->id); $s=NodeStore::siteSettings(); $refs=DB::table('users')->where('referral_by',$u->referral_code)->get(['id','username','created_at']); return response()->json(['affiliateCode'=>$u->referral_code,'affiliateBalance'=>NodeStore::publicUser($u)['affiliateBalance'],'commissionPercent'=>$s['affiliateCommissionPercent'],'minWithdraw'=>$s['minAffiliateWithdraw'],'referrals'=>$refs->map(fn($x)=>['id'=>(int)$x->id,'username'=>$x->username,'totalDeposit'=>0,'createdAt'=>$x->created_at])->values()]); }
    public function affiliateWithdraw(Request $r){ $u=$this->requireUser($r); return response()->json(['message'=>'No affiliate balance','user'=>NodeStore::publicUser($u->fresh()),'data'=>[]]); }
    public function notifications(Request $r){ $u=$this->requireUser($r); $rows=DB::table('notifications_v2')->where('user_id',$u->id)->orderByDesc('id')->limit(50)->get(); return response()->json(['notifications'=>$rows->map(fn($n)=>['id'=>(int)$n->id,'userId'=>(int)$n->user_id,'title'=>$n->title,'message'=>$n->message,'type'=>$n->type,'read'=>(bool)$n->read,'createdAt'=>$n->created_at])->values()]); }
    public function notificationsRead(Request $r){ $u=$this->requireUser($r); $q=DB::table('notifications_v2')->where('user_id',$u->id); if($r->filled('id')) $q->where('id',(int)$r->input('id')); $q->update(['read'=>1]); return response()->json(['ok'=>true]); }
    public function search(Request $r){ $u=$this->requireUser($r); $qRaw=strtolower(trim((string)$r->query('q',''))); if($qRaw==='') return response()->json(['services'=>[],'orders'=>[],'posts'=>[],'categories'=>[],'members'=>[]]); $q="%$qRaw%"; $services=DB::table('services_v2')->where('status',1)->where(fn($w)=>$w->whereRaw('lower(name) like ?',[$q])->orWhereRaw('lower(product_code) like ?',[$q]))->limit(8)->get()->map(fn($x)=>NodeStore::mapService($x))->values(); $orders=DB::table('orders_v2')->where('user_id',$u->id)->where(fn($w)=>$w->whereRaw('lower(order_code) like ?',[$q])->orWhereRaw('lower(service_name) like ?',[$q]))->limit(6)->get()->map(fn($x)=>NodeStore::mapOrder($x))->values(); return response()->json(['services'=>$services,'orders'=>$orders,'posts'=>[],'categories'=>[],'members'=>[]]); }

    public function calculate(Request $r){ $serviceId=(int)$r->input('service_id'); $quantity=(int)$r->input('quantity'); $svc=DB::table('services_v2')->where('id',$serviceId)->first(); abort_unless($svc,404,'Service not found'); $s=NodeStore::mapService($svc); $total=round(($s['pricePer1k']/1000)*$quantity,2); return response()->json(['data'=>['total_payment'=>$total,'total_payment_formatted'=>number_format($total,2),'valid'=>$quantity>=$s['minBuy'] && $quantity<=$s['maxBuy'],'error'=>null],'status'=>200,'message'=>'OK']); }
    public function v2(Request $r){ $action=strtolower((string)$r->input('action',$r->query('action','services'))); $key=(string)$r->input('key',$r->query('key','')); $api=DB::table('api_keys')->where('key_hash',hash('sha256',$key))->where('revoked',0)->first(); abort_unless($api,401,'Invalid key'); $u=User::find($api->user_id); abort_unless($u,401,'Invalid key'); if($action==='services'){ $rows=DB::table('services_v2')->where('status',1)->get(); return response()->json($rows->map(fn($x)=>['service'=>(int)$x->id,'name'=>$x->name,'type'=>$x->type,'rate'=>NodeStore::fromMicro($x->price_per_1k_micro),'min'=>(int)$x->min_buy,'max'=>(int)$x->max_buy,'category'=>(string)$x->category_id])->values()); }
      if($action==='balance'){ NodeStore::ensureWallet($u->id); $bal=NodeStore::fromMicro(DB::table('wallets')->where('user_id',$u->id)->value('balance_micro')); return response()->json(['balance'=>number_format($bal,2,'.',''),'balancef'=>number_format($bal,2),'currency'=>$u->currency_code ?? 'THB']); }
      return response()->json(['error'=>'Invalid action'],400); }

    // Admin minimal stubs for SPA compatibility
    public function adminOverview(Request $r){ $this->requireAdmin($r); return response()->json(['users'=>User::count(),'orders'=>DB::table('orders_v2')->count(),'services'=>DB::table('services_v2')->count(),'revenue'=>NodeStore::fromMicro(DB::table('deposit_invoices_v2')->where('status','paid')->sum('net_amount_micro'))]); }
    public function adminOrderStatus(Request $r,$id){ $this->requireAdmin($r); DB::table('orders_v2')->where('id',(int)$id)->update(['status'=>$r->input('status','Pending'),'updated_at'=>now()]); return response()->json(['message'=>'Updated','order'=>NodeStore::mapOrder(DB::table('orders_v2')->where('id',(int)$id)->first())]); }
    public function adminInvoiceApprove(Request $r,$id){ $this->requireAdmin($r); $inv=DB::table('deposit_invoices_v2')->where('id',(int)$id)->first(); abort_unless($inv,404,'Not found'); if($inv->status!=='paid'){ DB::transaction(function() use($inv,$r){ DB::table('deposit_invoices_v2')->where('id',$inv->id)->update(['status'=>'paid','reviewed_by'=>$this->requireAdmin($r)->username,'paid_at'=>now()]); NodeStore::ensureWallet($inv->user_id); $w=DB::table('wallets')->where('user_id',$inv->user_id)->first(); $before=(int)$w->balance_micro; $after=$before+(int)$inv->net_amount_micro; DB::table('wallets')->where('user_id',$inv->user_id)->update(['balance_micro'=>$after,'total_deposit_micro'=>DB::raw('total_deposit_micro+'.(int)$inv->net_amount_micro),'updated_at'=>now()]); DB::table('transactions_v2')->insert(['code'=>NodeStore::makeCode('TRX'),'user_id'=>$inv->user_id,'username'=>$inv->username,'type'=>'deposit','amount_micro'=>(int)$inv->net_amount_micro,'balance_before_micro'=>$before,'balance_after_micro'=>$after,'status'=>'paid','content'=>"Deposit {$inv->code}",'created_at'=>now()]); }); } $inv=DB::table('deposit_invoices_v2')->where('id',(int)$id)->first(); return response()->json(['message'=>'Approved','invoice'=>['id'=>(int)$inv->id,'code'=>$inv->code,'status'=>$inv->status]]); }
    public function adminInvoiceReject(Request $r,$id){ $this->requireAdmin($r); DB::table('deposit_invoices_v2')->where('id',(int)$id)->update(['status'=>'cancelled','reviewed_by'=>$r->user()->username]); $inv=DB::table('deposit_invoices_v2')->where('id',(int)$id)->first(); return response()->json(['message'=>'Rejected','invoice'=>['id'=>(int)$inv->id,'code'=>$inv->code,'status'=>$inv->status]]); }
    public function adminCreateService(Request $r){ $this->requireAdmin($r); $id=DB::table('services_v2')->insertGetId(['category_id'=>(int)$r->input('categoryId',1),'platform_id'=>(int)$r->input('platformId',1),'product_code'=>$r->input('productCode','PRD-'.Str::upper(Str::random(4))),'name'=>$r->input('name','Service'),'price_per_1k_micro'=>NodeStore::micro($r->input('pricePer1k',1)),'original_price_per_1k_micro'=>0,'min_buy'=>(int)$r->input('minBuy',100),'max_buy'=>(int)$r->input('maxBuy',10000),'type'=>$r->input('type','default'),'add_type'=>'manual','refill_support'=>0,'cancel_support'=>0,'speed_label'=>$r->input('speedLabel','Fast'),'average_time'=>$r->input('averageTime','15 นาที'),'description'=>$r->input('description',''),'is_demo'=>0,'status'=>1]); return response()->json(['message'=>'Created','service'=>NodeStore::mapService(DB::table('services_v2')->where('id',$id)->first())],201); }
    public function adminUpdateService(Request $r,$id){ $this->requireAdmin($r); $payload=[]; foreach(['name'=>'name','type'=>'type','speedLabel'=>'speed_label','averageTime'=>'average_time','description'=>'description'] as $in=>$col){ if($r->has($in)) $payload[$col]=$r->input($in);} if($r->has('pricePer1k')) $payload['price_per_1k_micro']=NodeStore::micro($r->input('pricePer1k')); if($r->has('minBuy')) $payload['min_buy']=(int)$r->input('minBuy'); if($r->has('maxBuy')) $payload['max_buy']=(int)$r->input('maxBuy'); if($r->has('status')) $payload['status']=$r->boolean('status')?1:0; if($payload) DB::table('services_v2')->where('id',(int)$id)->update($payload); return response()->json(['message'=>'Updated','service'=>NodeStore::mapService(DB::table('services_v2')->where('id',(int)$id)->first())]); }
    public function adminDeleteService(Request $r,$id){ $this->requireAdmin($r); DB::table('services_v2')->where('id',(int)$id)->delete(); return response()->json(['message'=>'Deleted']); }
    public function adminCreatePlatform(Request $r){ $this->requireAdmin($r); $id=DB::table('platforms_v2')->insertGetId(['name'=>$r->input('name'),'slug'=>Str::slug($r->input('name','platform')).'-'.Str::random(4),'code'=>strtoupper(substr($r->input('name','PL'),0,2)),'badge_color'=>$r->input('badgeColor','#2563eb'),'status'=>1,'priority'=1]); $p=DB::table('platforms_v2')->where('id',$id)->first(); return response()->json(['message'=>'Created','platform'=>['id'=>(int)$p->id,'name'=>$p->name,'slug'=>$p->slug,'code'=>$p->code,'badgeColor'=>$p->badge_color,'status'=>(bool)$p->status,'priority'=>(int)$p->priority]],201); }
    public function adminCreateCategory(Request $r){ $this->requireAdmin($r); $id=DB::table('categories_v2')->insertGetId(['platform_id'=>(int)$r->input('platformId',1),'name'=>$r->input('name','Category'),'slug'=>Str::slug($r->input('name','category')).'-'.Str::random(4),'status'=>1,'priority'=>1]); $c=DB::table('categories_v2')->where('id',$id)->first(); return response()->json(['message'=>'Created','category'=>['id'=>(int)$c->id,'platformId'=>(int)$c->platform_id,'name'=>$c->name,'slug'=>$c->slug,'status'=>(bool)$c->status,'priority'=>(int)$c->priority]],201); }
    public function adminAdjustUser(Request $r,$id){ $this->requireAdmin($r); $user=User::findOrFail((int)$id); NodeStore::ensureWallet($user->id); $amount=(float)$r->input('amount',0); if($amount!=0){ $w=DB::table('wallets')->where('user_id',$user->id)->first(); $before=(int)$w->balance_micro; $after=$before+NodeStore::micro($amount); DB::table('wallets')->where('user_id',$user->id)->update(['balance_micro'=>max(0,$after),'updated_at'=>now()]); DB::table('transactions_v2')->insert(['code'=>NodeStore::makeCode('TRX'),'user_id'=>$user->id,'username'=>$user->username,'type'=>'admin-adjust','amount_micro'=>NodeStore::micro($amount),'balance_before_micro'=>$before,'balance_after_micro'=>max(0,$after),'status'=>'paid','content'=>$r->input('reason','Admin adjust'),'created_at'=>now()]); } return response()->json(['message'=>'Updated','user'=>NodeStore::publicUser($user->fresh())]); }
    public function adminResetToken(Request $r,$id){ $this->requireAdmin($r); $user=User::findOrFail((int)$id); $token=Str::random(40); DB::table('password_reset_tokens_v2')->insert(['user_id'=>$user->id,'token_hash'=>hash('sha256',$token),'expires_at'=>now()->addMinutes(30),'used_at'=>null,'created_at'=>now()]); return response()->json(['message'=>'Reset token created','username'=>$user->username,'token'=>$token,'expiresInMinutes'=>30]); }
    public function adminCreateBank(Request $r){ $this->requireAdmin($r); $id=DB::table('bank_accounts_v2')->insertGetId(['bank_name'=>$r->input('bankName'),'short_name'=>$r->input('shortName','BANK'),'account_name'=>$r->input('accountName'),'account_number'=>$r->input('accountNumber'),'prompt_pay_id'=>$r->input('promptPayId',''),'min_deposit_micro'=>NodeStore::micro($r->input('minDeposit',10)),'status'=>1]); $b=DB::table('bank_accounts_v2')->where('id',$id)->first(); return response()->json(['message'=>'Created','bank'=>['id'=>(int)$b->id,'bankName'=>$b->bank_name,'shortName'=>$b->short_name,'accountName'=>$b->account_name,'accountNumber'=>$b->account_number,'promptPayId'=>$b->prompt_pay_id,'minDeposit'=>NodeStore::fromMicro($b->min_deposit_micro),'status'=>(bool)$b->status]],201); }
    public function adminUpdateBank(Request $r,$id){ $this->requireAdmin($r); DB::table('bank_accounts_v2')->where('id',(int)$id)->update(['bank_name'=>$r->input('bankName'),'short_name'=>$r->input('shortName','BANK'),'account_name'=>$r->input('accountName'),'account_number'=>$r->input('accountNumber'),'prompt_pay_id'=>$r->input('promptPayId',''),'status'=>$r->boolean('status',true)?1:0]); $b=DB::table('bank_accounts_v2')->where('id',(int)$id)->first(); return response()->json(['message'=>'Updated','bank'=>['id'=>(int)$b->id,'bankName'=>$b->bank_name,'shortName'=>$b->short_name,'accountName'=>$b->account_name,'accountNumber'=>$b->account_number,'promptPayId'=>$b->prompt_pay_id,'minDeposit'=>NodeStore::fromMicro($b->min_deposit_micro),'status'=>(bool)$b->status]]); }
    public function adminDeleteBank(Request $r,$id){ $this->requireAdmin($r); DB::table('bank_accounts_v2')->where('id',(int)$id)->delete(); return response()->json(['message'=>'Deleted']); }
    public function adminCreateVoucher(Request $r){ $this->requireAdmin($r); $id=DB::table('vouchers_v2')->insertGetId(['code'=>strtoupper($r->input('code','NV'.Str::random(6))),'value_micro'=>NodeStore::micro($r->input('value',0)),'max_uses'=>(int)$r->input('maxUses',1),'used_count'=>0,'allowed_ranks_json'=>json_encode($r->input('allowedRanks',[])),'expires_at'=>$r->input('expiresAt',now()->addYear()),'status'=>1]); $v=DB::table('vouchers_v2')->where('id',$id)->first(); return response()->json(['message'=>'Created','voucher'=>['id'=>(int)$v->id,'code'=>$v->code,'value'=>NodeStore::fromMicro($v->value_micro),'maxUses'=>(int)$v->max_uses,'usedCount'=>(int)$v->used_count,'usedBy'=>[],'allowedRanks'=>json_decode($v->allowed_ranks_json?:'[]',true),'expiresAt'=>$v->expires_at,'status'=>(bool)$v->status]],201); }
    public function adminDeleteVoucher(Request $r,$id){ $this->requireAdmin($r); DB::table('vouchers_v2')->where('id',(int)$id)->delete(); return response()->json(['message'=>'Deleted']); }
    public function adminCreateProvider(Request $r){ $this->requireAdmin($r); return response()->json(['message'=>'Not implemented in PHP port yet','provider'=>null],501); }
    public function adminToggleProvider(Request $r,$id){ $this->requireAdmin($r); return response()->json(['message'=>'Not implemented'],501); }
    public function adminProviderBalance(Request $r,$id){ $this->requireAdmin($r); return response()->json(['message'=>'Not implemented'],501); }
    public function adminProviderSync(Request $r,$id){ $this->requireAdmin($r); return response()->json(['message'=>'Not implemented'],501); }
    public function adminCreateAnnouncement(Request $r){ $this->requireAdmin($r); $id=DB::table('announcements_v2')->insertGetId(['title'=>$r->input('title'),'content'=>$r->input('content'),'level'=>$r->input('level','promo'),'active'=>1,'created_at'=>now()]); $a=DB::table('announcements_v2')->where('id',$id)->first(); return response()->json(['message'=>'Created','announcement'=>['id'=>(int)$a->id,'title'=>$a->title,'content'=>$a->content,'level'=>$a->level,'active'=>(bool)$a->active,'createdAt'=>$a->created_at]],201); }
    public function adminDeleteAnnouncement(Request $r,$id){ $this->requireAdmin($r); DB::table('announcements_v2')->where('id',(int)$id)->delete(); return response()->json(['message'=>'Deleted']); }
    public function adminSettings(Request $r){ $this->requireAdmin($r); foreach($r->except(['_token']) as $k=>$v){ NodeStore::setSetting($k,$v); } return response()->json(['message'=>'Saved','siteSettings'=>NodeStore::siteSettings()]); }
    public function cronSyncOrders(Request $r){ return response()->json(['queued'=>0]); }


