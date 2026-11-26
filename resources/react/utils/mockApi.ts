import type { User, Platform, Category, Service, Post, RankTierConfig, SiteSettings, Order, Transaction, DepositInvoice, BankAccount, NotificationItem } from '../types/smm';

const K='novasmm_static_v1';
const now=()=>new Date().toISOString();
function load(){ try{const r=localStorage.getItem(K); return r?JSON.parse(r):null;}catch{return null;} }
function save(db:any){ localStorage.setItem(K,JSON.stringify(db)); }
function uid(db:any,table:string){ db._seq[table]=(db._seq[table]||0)+1; return db._seq[table]; }
function micro(n:number){ return Math.round(n*1_000_000); }
function fromMicro(n:number){ return Math.round(n)/1_000_000; }

function seed(){
  const platforms:Platform[]=[
    {id:1,name:'Facebook',slug:'facebook',code:'FB',badgeColor:'#1877f2',status:true,priority:1},
    {id:2,name:'Instagram',slug:'instagram',code:'IG',badgeColor:'#e1306c',status:true,priority:2},
    {id:3,name:'TikTok',slug:'tiktok',code:'TT',badgeColor:'#111827',status:true,priority:3},
    {id:4,name:'YouTube',slug:'youtube',code:'YT',badgeColor:'#ff0000',status:true,priority:4},
  ];
  const categories:Category[]=[
    {id:1,platformId:1,name:'Facebook Followers',slug:'fb-followers',status:true,priority:1},
    {id:2,platformId:2,name:'Instagram Likes',slug:'ig-likes',status:true,priority:2},
    {id:3,platformId:3,name:'TikTok Views',slug:'tt-views',status:true,priority:3},
    {id:4,platformId:4,name:'YouTube Subscribers',slug:'yt-subs',status:true,priority:4},
  ];
  const services:Service[]=[
    {id:1,productCode:'PRD-001',categoryId:1,platformId:1,name:'Facebook Page Followers [Real]',pricePer1k:120,originalPricePer1k:60,minBuy:100,maxBuy:100000,type:'default',addType:'manual',apiProviderId:null,apiServiceId:null,refillSupport:true,cancelSupport:true,speedLabel:'5K/วัน',averageTime:'30 นาที',description:'เพิ่มผู้ติดตามเพจ Facebook',status:true},
    {id:2,productCode:'PRD-002',categoryId:2,platformId:2,name:'Instagram Likes [HQ]',pricePer1k:35,originalPricePer1k:15,minBuy:50,maxBuy:200000,type:'default',addType:'manual',apiProviderId:null,apiServiceId:null,refillSupport:false,cancelSupport:true,speedLabel:'20K/วัน',averageTime:'10 นาที',description:'เพิ่มไลก์ Instagram',status:true},
    {id:3,productCode:'PRD-003',categoryId:3,platformId:3,name:'TikTok Video Views',pricePer1k:8,originalPricePer1k:2,minBuy:100,maxBuy:10000000,type:'default',addType:'manual',apiProviderId:null,apiServiceId:null,refillSupport:false,cancelSupport:false,speedLabel:'100K/วัน',averageTime:'5 นาที',description:'เพิ่มยอดวิว TikTok',status:true},
    {id:4,productCode:'PRD-004',categoryId:4,platformId:4,name:'YouTube Subscribers [Refill 30D]',pricePer1k:450,originalPricePer1k:250,minBuy:50,maxBuy:50000,type:'default',addType:'manual',apiProviderId:null,apiServiceId:null,refillSupport:true,cancelSupport:false,speedLabel:'1K/วัน',averageTime:'1 ชม.',description:'เพิ่มผู้ติดตาม YouTube',status:true},
  ];
  const settings:SiteSettings={siteName:'NovaSMM',siteTagline:'แพลตฟอร์ม SMM อัตโนมัติ 24/7',siteDescription:'Static demo on GitHub Pages',announcement:'นี่คือโหมด demo บน GitHub Pages (ข้อมูลเก็บในเบราว์เซอร์)',affiliateCommissionPercent:5,minAffiliateWithdraw:100,welcomeBonusAmount:0,depositMethods:{bank:true,card:false,crypto:false,paypal:false,perfect_money:false},contactFacebook:'',contactTelegram:'',contactLine:''};
  return {
    _seq:{users:1,orders:100,transactions:100,invoices:100,notifications:1,audit:1},
    users:[{id:1,name:'Admin',username:'admin',email:'admin@novasmm.local',password:'admin1234',role:'admin',rank:'agency',status:'active',balance:5000,totalDeposit:5000,totalSpent:0,affiliateBalance:0,affiliateCode:'ADMIN001',referredBy:null,apiKeyPrefix:'',hasApiKey:false,currency:'THB',language:'th',createdAt:now(),lastLoginAt:now()}],
    platforms,categories,services,orders:[],transactions:[],invoices:[],notifications:[],posts:[],vouchers:[{id:1,code:'WELCOME50',value:50,maxUses:100,usedCount:0,usedBy:[],allowedRanks:[],expiresAt:'2099-12-31T00:00:00.000Z',status:true}],
    banks:[{id:1,bankName:'กสิกรไทย',shortName:'KBANK',accountName:'NovaSMM Demo',accountNumber:'000-0-00000-0',promptPayId:'0812345678',minDeposit:10,status:true}],
    settings, currentUserId: null,
  };
}
function db(){ const d=load()||seed(); save(d); return d; }
function me(d:any){ return d.users.find((u:any)=>u.id===d.currentUserId)||null; }
function pub(u:any):User{ const {password,...p}=u; return p; }

export async function mockApi(urlStr:string, options:RequestInit={}): Promise<any> {
  const d=db(); const method=(options.method||'GET').toUpperCase();
  const u=new URL(urlStr, (import.meta.env.BASE_URL || '/')); const p=u.pathname; const q=Object.fromEntries(u.searchParams.entries());
  let body:any={}; try{ body=options.body?JSON.parse(String(options.body)):{}; }catch{}
  const user=me(d);
  const ok=(data:any,status=200)=>({__mock:true,status,data});
  const err=(message:string,status=400,extra:any={})=>{ const e:any=new Error(message); e.status=status; e.data={message,...extra}; throw e; };

  if(p==='/api/health') return ok({ok:true,service:'novasmm-static'});
  if(p==='/api/bootstrap'){
    const needsAdminSetup=!d.users.some((x:any)=>x.role==='admin');
    const base={siteSettings:{siteName:d.settings.siteName,siteTagline:d.settings.siteTagline,siteDescription:d.settings.siteDescription,contactFacebook:d.settings.contactFacebook,contactTelegram:d.settings.contactTelegram,contactLine:d.settings.contactLine}, needsAdminSetup, publicStats:{totalUsers:d.users.length,totalServices:d.services.filter((s:any)=>s.status).length,totalOrdersCompleted:d.orders.filter((o:any)=>o.status==='Completed').length,ordersToday:d.orders.length,totalUsersCount:d.users.length,uptimePercent:99.9}};
    if(!user) return ok(base);
    const notifications=d.notifications.filter((n:any)=>n.userId===user.id).slice(-20).reverse();
    return ok({...base,user:pub(user),platforms:d.platforms,categories:d.categories,services:d.services,posts:d.posts,rankTiers:[{rank:'member',label:'Member',discountPercent:0,minSpentThreshold:0},{rank:'silver',label:'Silver',discountPercent:1,minSpentThreshold:1000},{rank:'gold',label:'Gold',discountPercent:2,minSpentThreshold:5000},{rank:'platinum',label:'Platinum',discountPercent:4,minSpentThreshold:20000},{rank:'agency',label:'Agency',discountPercent:6,minSpentThreshold:50000}],announcements:[],notifications,siteSettings:d.settings});
  }

  if(p==='/api/auth/login'&&method==='POST'){ const found=d.users.find((x:any)=>x.email===body.identifier||x.username===body.identifier); if(!found||found.password!==body.password) err('Invalid credentials',401); d.currentUserId=found.id; save(d); return ok({user:pub(found)}); }
  if(p==='/api/auth/register'&&method==='POST'){ if(d.users.some((x:any)=>x.email===body.email||x.username===body.username)) err('User exists',409); const nu={id:uid(d,'users'),name:body.name,username:body.username,email:body.email,password:body.password,role:'user',rank:'member',status:'active',balance:0,totalDeposit:0,totalSpent:0,affiliateBalance:0,affiliateCode:('REF'+Math.random().toString(36).slice(2,8)).toUpperCase(),referredBy:body.ref||null,apiKeyPrefix:'',hasApiKey:false,currency:'THB',language:'th',createdAt:now(),lastLoginAt:now()}; d.users.push(nu); d.currentUserId=nu.id; save(d); return ok({user:pub(nu)},201); }
  if(p==='/api/auth/setup-admin'&&method==='POST'){ if(d.users.some((x:any)=>x.role==='admin')) err('Admin exists',409); const nu={id:uid(d,'users'),name:body.name,username:body.username,email:body.email,password:body.password,role:'admin',rank:'agency',status:'active',balance:0,totalDeposit:0,totalSpent:0,affiliateBalance:0,affiliateCode:'ADMIN'+Math.random().toString(36).slice(2,6).toUpperCase(),referredBy:null,apiKeyPrefix:'',hasApiKey:false,currency:'THB',language:'th',createdAt:now(),lastLoginAt:now()}; d.users.push(nu); d.currentUserId=nu.id; d.settings.siteName=body.siteName||d.settings.siteName; save(d); return ok({user:pub(nu)},201); }
  if(p==='/api/auth/logout'&&method==='POST'){ d.currentUserId=null; save(d); return ok({ok:true}); }
  if(p==='/api/auth/me') return ok({user:user?pub(user):null});

  if(!user) err('Unauthorized',401);

  if(p==='/api/orders'&&method==='GET'){ const rows=d.orders.filter((o:any)=>o.userId===user.id).slice().reverse(); return ok({orders:rows}); }
  if((p==='/api/orders'||p==='/api/orders/store')&&method==='POST'){
    const svc=d.services.find((s:any)=>s.id===Number(body.serviceId||body.service_id)); if(!svc) err('Service not found',404);
    const qty=Number(body.quantity||0); const link=String(body.objectId||body.object_id||body.link||'').trim();
    if(!qty||!link) err('Invalid order',422);
    const total=Math.round((svc.pricePer1k/1000)*qty*100)/100; const charge=user.role==='admin'?0:total;
    if(user.balance<charge) err('ยอดเงินไม่พอ',422);
    const before=user.balance; user.balance=Math.round((user.balance-charge)*100)/100; user.totalSpent=Math.round((user.totalSpent+total)*100)/100;
    const order={id:uid(d,'orders'),orderCode:'ORD'+Date.now(),userId:user.id,username:user.username,serviceId:svc.id,categoryId:svc.categoryId,platformId:svc.platformId,serviceName:svc.name,objectId:link,quantity:qty,startNumber:0,remains:qty,successCount:0,pricePer1k:svc.pricePer1k,discountPercent:0,totalPayment:total,providerCost:0,providerId:null,providerOrderId:null,providerError:null,status:'Pending',orderNote:body.orderNote||body.order_note||'',comments:[],reactionType:null,refillRequested:false,refillStatus:'none',refundedAmount:0,createdAt:now(),updatedAt:now()};
    d.orders.push(order);
    d.transactions.push({id:uid(d,'transactions'),code:'TRX'+Date.now(),userId:user.id,username:user.username,type:'new-order',amount:-charge,balanceBefore:before,balanceAfter:user.balance,status:'paid',content:`Order ${order.orderCode}`,createdAt:now()});
    save(d); return ok({message:'สร้างคำสั่งซื้อสำเร็จ',orders:[order],user:pub(user)},201);
  }

  if(p==='/api/deposits'&&method==='GET'){ return ok({banks:d.banks,invoices:d.invoices.filter((i:any)=>i.userId===user.id).slice().reverse()}); }
  if(p==='/api/deposits/invoice'&&method==='POST'){ const amt=Number(body.amount||0); if(amt<=0) err('Invalid amount',422); const inv={id:uid(d,'invoices'),code:'INV'+Date.now(),userId:user.id,username:user.username,method:body.method||'bank',amount:amt,fee:0,netAmount:amt,channelName:body.channelName||'Bank',reference:'',proofNote:body.proofNote||'',status:'pending',reviewedBy:null,createdAt:now(),paidAt:null}; d.invoices.push(inv); save(d); return ok({message:'สร้างใบแจ้งหนี้สำเร็จ',invoice:inv,user:pub(user)},201); }
  if(p==='/api/deposits/voucher'&&method==='POST'){ const code=String(body.code||'').toUpperCase(); const v=d.vouchers.find((x:any)=>x.code.toUpperCase()===code&&x.status); if(!v) err('Voucher not found',404); if(v.usedBy.includes(user.username)) err('Already used',422); v.usedBy.push(user.username); v.usedCount++; const before=user.balance; user.balance=Math.round((user.balance+v.value)*100)/100; d.transactions.push({id:uid(d,'transactions'),code:'TRX'+Date.now(),userId:user.id,username:user.username,type:'gift-code',amount:v.value,balanceBefore:before,balanceAfter:user.balance,status:'paid',content:`Redeem ${v.code}`,createdAt:now()}); save(d); return ok({message:'Redeemed',user:pub(user),data:{code:v.code,value:v.value}}); }

  if(p==='/api/transactions'||p==='/api/users/transactions') return ok({transactions:d.transactions.filter((t:any)=>t.userId===user.id).slice().reverse(),data:d.transactions.filter((t:any)=>t.userId===user.id).slice().reverse(),status:200,message:'OK'});
  if(p==='/api/notifications') return ok({notifications:d.notifications.filter((n:any)=>n.userId===user.id).slice().reverse()});
  if(p==='/api/notifications/read'&&method==='POST'){ d.notifications.filter((n:any)=>n.userId===user.id).forEach((n:any)=>n.read=true); save(d); return ok({ok:true}); }
  if(p==='/api/profile/update'&&method==='POST'){ if(body.name) user.name=body.name; if(body.language) user.language=body.language; if(body.currency) user.currency=body.currency; save(d); return ok({message:'บันทึกสำเร็จ',user:pub(user)}); }
  if(p==='/api/profile/password'&&method==='POST'){ if(user.password!==body.currentPassword) err('รหัสผ่านเดิมไม่ถูกต้อง',422); user.password=body.newPassword; save(d); return ok({message:'เปลี่ยนรหัสผ่านสำเร็จ'}); }
  if(p==='/api/profile/token'&&method==='POST'){ const raw='nv_live_'+Math.random().toString(36).slice(2)+Math.random().toString(36).slice(2); user.apiKeyPrefix=raw.slice(0,12)+'...'+raw.slice(-4); user.hasApiKey=true; save(d); return ok({message:'สร้าง API Key แล้ว',user:pub(user),rawApiKey:raw}); }
  if(p==='/api/profile/token/revoke'&&method==='POST'){ user.hasApiKey=false; user.apiKeyPrefix=''; save(d); return ok({message:'เพิกถอนแล้ว',user:pub(user)}); }
  if(p==='/api/affiliates') return ok({affiliateCode:user.affiliateCode,affiliateBalance:user.affiliateBalance,commissionPercent:d.settings.affiliateCommissionPercent,minWithdraw:d.settings.minAffiliateWithdraw,referrals:[]});
  if(p==='/api/affiliates/withdraw'&&method==='POST') return ok({message:'No balance',user:pub(user)});
  if(p==='/api/search'){ const q=(qparam('q')||'').toLowerCase(); function qparam(k:string){return u.searchParams.get(k)||'';} const services=d.services.filter((s:any)=>s.name.toLowerCase().includes(q)).slice(0,8); const orders=d.orders.filter((o:any)=>o.userId===user.id && (o.orderCode.toLowerCase().includes(q)||o.serviceName.toLowerCase().includes(q))).slice(0,6); return ok({services,orders,posts:[],categories:[],members:[]}); }

  // Admin minimal
  if(p.startsWith('/api/admin/')){ if(user.role!=='admin') err('Forbidden',403);
    if(p==='/api/admin/overview') return ok({users:d.users.length,orders:d.orders.length,services:d.services.length,revenue:d.invoices.filter((i:any)=>i.status==='paid').reduce((a:number,b:any)=>a+b.netAmount,0),recentOrders:d.orders.slice(-10).reverse(),recentUsers:d.users.slice(-10).reverse(),invoices:d.invoices.slice(-20).reverse()});
    if(p==='/api/admin/settings'&&method==='POST'){ Object.assign(d.settings, body); save(d); return ok({message:'Saved',siteSettings:d.settings}); }
  }

  // preset/tools compat
  if(p==='/api/preset-data/platforms') return ok({data:d.platforms.filter((x:any)=>x.status).map((x:any)=>({id:x.id,name:x.name,code:x.code,status:x.status,image:null})),status:200,message:'OK'});
  if(p==='/api/preset-data/categories'){ let rows=d.categories.filter((x:any)=>x.status); if(q.platform_id) rows=rows.filter((x:any)=>String(x.platformId)===String(q.platform_id)); return ok({data:rows.map((x:any)=>({id:x.id,platform_id:x.platformId,name:x.name,status:x.status,image:null})),status:200,message:'OK'}); }
  if(p==='/api/preset-data/services') return ok({data:d.services.filter((s:any)=>s.status).map((s:any)=>({id:s.id,category_id:s.categoryId,name:s.name,display_name:`[${s.productCode}] ${s.name}`,price_per:s.pricePer1k/1000,price:s.pricePer1k/1000,price_formatted:(s.pricePer1k/1000).toFixed(2),min_buy:s.minBuy,max_buy:s.maxBuy,type:s.type,refill:s.refillSupport,cancel:s.cancelSupport,status:s.status,image:null,descr:s.description,average_time:s.averageTime,text:s.name})),status:200,message:'OK'});
  if(p==='/api/tools/calculate'&&method==='POST'){ const svc=d.services.find((s:any)=>s.id===Number(body.service_id)); if(!svc) err('Not found',404); const total=Math.round((svc.pricePer1k/1000)*Number(body.quantity||0)*100)/100; return ok({data:{total_payment:total,total_payment_formatted:total.toFixed(2),valid:true,error:null},status:200,message:'OK'}); }

  err(`Mock API not implemented: ${method} ${p}`, 501);
}

