import { useEffect, useMemo, useState, type FormEvent } from 'react';
import {
  AlertCircle,
  CheckCircle2,
  Clock,
  Layers,
  ShieldCheck,
  ShoppingCart,
  Zap,
} from 'lucide-react';
import type { Category, CurrencyCode, Platform, RankTierConfig, Service, User } from '../types/smm';
import { calculateOrderPricing, formatCurrency } from '../utils/pricing';
import { apiRequest } from '../utils/api';
import { PlatformBadge } from '../components/PlatformBadge';

interface NewOrderPageProps {
  user: User;
  currency: CurrencyCode;
  platforms: Platform[];
  categories: Category[];
  services: Service[];
  rankTiers: RankTierConfig[];
  preselectedServiceId?: number | null;
  onUserUpdate: (u: User) => void;
  onGoOrders: () => void;
}

export function NewOrderPage({
  user,
  currency,
  platforms,
  categories,
  services,
  rankTiers,
  preselectedServiceId,
  onUserUpdate,
  onGoOrders,
}: NewOrderPageProps) {
  const [selectedPlatformId, setSelectedPlatformId] = useState<number>(0);
  const [selectedCategoryId, setSelectedCategoryId] = useState<number>(0);
  const [selectedServiceId, setSelectedServiceId] = useState<number>(services[0]?.id ?? 0);

  const [isMultiple, setIsMultiple] = useState(false);
  const [objectId, setObjectId] = useState('');
  const [quantity, setQuantity] = useState<number>(1000);
  const [comments, setComments] = useState('');
  const [reactionType, setReactionType] = useState('love');
  const [orderNote, setOrderNote] = useState('');

  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [successMsg, setSuccessMsg] = useState('');

  useEffect(() => {
    if (preselectedServiceId) {
      const svc = services.find((s) => s.id === preselectedServiceId);
      if (svc) {
        setSelectedPlatformId(svc.platformId);
        setSelectedCategoryId(svc.categoryId);
        setSelectedServiceId(svc.id);
        setQuantity(Math.max(svc.minBuy, Math.min(1000, svc.maxBuy)));
      }
    }
  }, [preselectedServiceId, services]);

  const filteredCategories = useMemo(() => {
    if (selectedPlatformId === 0) return categories;
    return categories.filter((c) => c.platformId === selectedPlatformId);
  }, [categories, selectedPlatformId]);

  const filteredServices = useMemo(() => {
    return services.filter((s) => {
      if (selectedPlatformId !== 0 && s.platformId !== selectedPlatformId) return false;
      if (selectedCategoryId !== 0 && s.categoryId !== selectedCategoryId) return false;
      return true;
    });
  }, [services, selectedPlatformId, selectedCategoryId]);

  const activeService = useMemo(() => {
    return (
      filteredServices.find((s) => s.id === selectedServiceId) ??
      filteredServices[0] ??
      services[0]
    );
  }, [filteredServices, selectedServiceId, services]);

  useEffect(() => {
    if (activeService && activeService.id !== selectedServiceId) {
      setSelectedServiceId(activeService.id);
      setQuantity(Math.max(activeService.minBuy, Math.min(1000, activeService.maxBuy)));
    }
  }, [activeService, selectedServiceId]);

  const pricing = useMemo(() => {
    if (!activeService) return null;
    const previewLinks = objectId.trim() ? objectId : 'https://preview.local';
    return calculateOrderPricing({
      service: activeService,
      rank: user.rank,
      role: user.role,
      customTiers: rankTiers,
      rawLinks: previewLinks,
      rawQuantity: quantity,
      rawComments: comments,
      isMultiple,
    });
  }, [activeService, user.rank, user.role, rankTiers, objectId, quantity, comments, isMultiple]);

  const activePlatform = platforms.find((p) => p.id === activeService?.platformId);

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    if (!activeService) return;
    setError('');
    setSuccessMsg('');
    setSubmitting(true);
    try {
      const idempotencyKey =
        typeof crypto !== 'undefined' && crypto.randomUUID
          ? crypto.randomUUID()
          : `ord-${Date.now()}-${Math.random().toString(36).slice(2)}`;
      const res = await apiRequest<{ message: string; user: User }>('/api/orders', {
        method: 'POST',
        body: JSON.stringify({
          serviceId: activeService.id,
          objectId,
          quantity,
          comments,
          reactionType,
          orderNote,
          isMultiple,
          idempotencyKey,
        }),
      });
      onUserUpdate(res.user);
      setSuccessMsg(res.message);
      setObjectId('');
      setComments('');
      setOrderNote('');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'สั่งซื้อไม่สำเร็จ');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="space-y-5">
      {/* Top KPI Cards */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
        <div className="nova-card p-4">
          <div className="text-xs text-slate-500 font-medium">ยอดเงินคงเหลือ</div>
          <div className="text-xl font-extrabold text-blue-600 mt-1">
            {formatCurrency(user.balance, currency)}
          </div>
        </div>
        <div className="nova-card p-4">
          <div className="text-xs text-slate-500 font-medium">ยอดใช้จ่ายสะสม</div>
          <div className="text-xl font-extrabold text-[#1e3a5f] mt-1">
            {formatCurrency(user.totalSpent, currency)}
          </div>
        </div>
        <div className="nova-card p-4">
          <div className="text-xs text-slate-500 font-medium">ยอดเติมเงินรวม</div>
          <div className="text-xl font-extrabold text-emerald-600 mt-1">
            {formatCurrency(user.totalDeposit, currency)}
          </div>
        </div>
        <div className="nova-card p-4">
          <div className="text-xs text-slate-500 font-medium">ระดับสมาชิก & ส่วนลด</div>
          <div className="text-xl font-extrabold text-sky-600 mt-1 uppercase">
            {user.role === 'admin' ? 'ADMIN (ฟรี 100%)' : `${user.rank} (${pricing?.discountPercent ?? 0}%)`}
          </div>
        </div>
      </div>

      {/* Platform Filter */}
      <div className="nova-card p-4">
        <div className="text-xs font-bold text-slate-500 mb-2.5">เลือกแพลตฟอร์ม</div>
        <div className="flex flex-wrap gap-2">
          <button
            type="button"
            onClick={() => {
              setSelectedPlatformId(0);
              setSelectedCategoryId(0);
            }}
            className={`px-3.5 py-2 rounded-xl text-xs font-bold border transition cursor-pointer ${
              selectedPlatformId === 0
                ? 'bg-blue-600 text-white border-blue-600'
                : 'bg-[#f4f8fd] text-slate-700 border-[#dbe7f3] hover:bg-blue-50'
            }`}
          >
            ทุกแพลตฟอร์ม
          </button>
          {platforms.map((p) => (
            <button
              key={p.id}
              type="button"
              onClick={() => {
                setSelectedPlatformId(p.id);
                setSelectedCategoryId(0);
              }}
              className={`inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold border transition cursor-pointer ${
                selectedPlatformId === p.id
                  ? 'bg-blue-600 text-white border-blue-600'
                  : 'bg-white text-slate-700 border-[#dbe7f3] hover:bg-[#f4f8fd]'
              }`}
            >
              <span
                className="w-4 h-4 rounded text-[9px] font-black text-white grid place-items-center"
                style={{ backgroundColor: p.badgeColor }}
              >
                {p.code}
              </span>
              <span>{p.name}</span>
            </button>
          ))}
        </div>
      </div>

      {/* Order Form + Service Details */}
      <div className="grid lg:grid-cols-12 gap-5 items-start">
        <form onSubmit={handleSubmit} className="lg:col-span-7 nova-card p-6 space-y-4">
          <div className="flex items-center justify-between pb-3 border-b border-slate-100">
            <h1 className="text-lg font-extrabold flex items-center gap-2">
              <ShoppingCart className="w-5 h-5 text-blue-600" />
              สร้างคำสั่งซื้อใหม่
            </h1>
            <label className="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
              <input
                type="checkbox"
                checked={isMultiple}
                onChange={(e) => setIsMultiple(e.target.checked)}
                className="rounded border-slate-300 text-blue-600"
              />
              <span>สั่งหลายลิงก์พร้อมกัน</span>
            </label>
          </div>

          {error && (
            <div
              role="alert"
              className="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium flex items-center gap-2"
            >
              <AlertCircle className="w-4 h-4 shrink-0" />
              <span>{error}</span>
            </div>
          )}

          {successMsg && (
            <div className="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between gap-3">
              <div className="flex items-center gap-2">
                <CheckCircle2 className="w-4 h-4 shrink-0 text-emerald-600" />
                <span>{successMsg}</span>
              </div>
              <button
                type="button"
                onClick={onGoOrders}
                className="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold hover:bg-emerald-700 shrink-0 cursor-pointer"
              >
                ดูคำสั่งซื้อ
              </button>
            </div>
          )}

          <div>
            <label htmlFor="order-category" className="block text-xs font-bold text-slate-700 mb-1.5">
              หมวดหมู่บริการ
            </label>
            <select
              id="order-category"
              value={selectedCategoryId}
              onChange={(e) => setSelectedCategoryId(Number(e.target.value))}
              className="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
            >
              <option value={0}>ทั้งหมด ({filteredCategories.length} หมวดหมู่)</option>
              {filteredCategories.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.name}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label htmlFor="order-service" className="block text-xs font-bold text-slate-700 mb-1.5">
              บริการที่ต้องการ
            </label>
            <select
              id="order-service"
              value={activeService?.id ?? 0}
              onChange={(e) => {
                const nextId = Number(e.target.value);
                setSelectedServiceId(nextId);
                const svc = services.find((s) => s.id === nextId);
                if (svc) {
                  setQuantity(Math.max(svc.minBuy, Math.min(1000, svc.maxBuy)));
                }
              }}
              className="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
            >
              {filteredServices.map((s) => (
                <option key={s.id} value={s.id}>
                  [{s.productCode}] {s.name} ({formatCurrency(s.pricePer1k, currency)} / 1,000)
                </option>
              ))}
            </select>
          </div>

          <div>
            <label htmlFor="order-link" className="block text-xs font-bold text-slate-700 mb-1.5">
              {isMultiple ? 'ลิงก์เป้าหมาย (1 บรรทัดต่อ 1 ลิงก์)' : 'ลิงก์หรือไอดีเป้าหมาย'}
            </label>
            {isMultiple ? (
              <textarea
                id="order-link"
                rows={4}
                required
                value={objectId}
                onChange={(e) => setObjectId(e.target.value)}
                placeholder={'https://www.tiktok.com/@user/video/1\nhttps://www.tiktok.com/@user/video/2'}
                className="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
              />
            ) : (
              <input
                id="order-link"
                type="text"
                required
                value={objectId}
                onChange={(e) => setObjectId(e.target.value)}
                placeholder="https://www.tiktok.com/@username/video/..."
                className="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
              />
            )}
          </div>

          {activeService?.type === 'custom_comments' ? (
            <div>
              <label htmlFor="order-comments" className="block text-xs font-bold text-slate-700 mb-1.5">
                ข้อความคอมเมนต์ (1 บรรทัด = 1 คอมเมนต์ · นับอัตโนมัติ {pricing?.effectiveQuantity ?? 0} รายการ)
              </label>
              <textarea
                id="order-comments"
                rows={4}
                required
                value={comments}
                onChange={(e) => setComments(e.target.value)}
                placeholder={'สุดยอดเลยครับ\nชอบคลิปนี้มาก\nติดตามแล้วนะ'}
                className="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
              />
            </div>
          ) : (
            <div>
              <label htmlFor="order-quantity" className="block text-xs font-bold text-slate-700 mb-1.5">
                จำนวนที่สั่งซื้อ ({activeService?.minBuy.toLocaleString()} –{' '}
                {activeService?.maxBuy.toLocaleString()})
              </label>
              <input
                id="order-quantity"
                type="number"
                required
                min={activeService?.minBuy ?? 1}
                max={activeService?.maxBuy ?? 1000000}
                value={quantity}
                onChange={(e) => setQuantity(Number(e.target.value))}
                className="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
              />
            </div>
          )}

          {activeService?.type === 'reactions' && (
            <div>
              <label htmlFor="order-reaction" className="block text-xs font-bold text-slate-700 mb-1.5">
                รูปแบบความรู้สึก (Reaction)
              </label>
              <select
                id="order-reaction"
                value={reactionType}
                onChange={(e) => setReactionType(e.target.value)}
                className="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white"
              >
                <option value="like">👍 ถูกใจ (Like)</option>
                <option value="love">❤️ รักเลย (Love)</option>
                <option value="care">🤗 ห่วงใย (Care)</option>
                <option value="haha">😆 ฮ่าๆ (Haha)</option>
                <option value="wow">😮 ว้าว (Wow)</option>
              </select>
            </div>
          )}

          <div>
            <label htmlFor="order-note" className="block text-xs font-bold text-slate-700 mb-1.5">
              หมายเหตุเพิ่มเติม (ไม่บังคับ)
            </label>
            <input
              id="order-note"
              type="text"
              value={orderNote}
              onChange={(e) => setOrderNote(e.target.value)}
              placeholder="บันทึกช่วยจำสำหรับออเดอร์นี้"
              className="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
            />
          </div>

          <div className="p-4 rounded-2xl bg-[#f4f8fd] border border-[#dbe7f3] space-y-2 text-xs">
            <div className="flex justify-between text-slate-600">
              <span>ราคาต่อ 1,000 หน่วย</span>
              <span className="font-bold">
                {activeService ? formatCurrency(activeService.pricePer1k, currency) : '-'}
              </span>
            </div>
            <div className="flex justify-between text-slate-600">
              <span>
                {user.role === 'admin'
                  ? 'สิทธิ์แอดมินใช้งานฟรี (ADMIN 100%)'
                  : `ส่วนลดสมาชิก (${user.rank.toUpperCase()} ${pricing?.discountPercent ?? 0}%)`}
              </span>
              <span className="font-bold text-emerald-600">
                -{formatCurrency(pricing?.discountAmount ?? 0, currency)}
              </span>
            </div>
            <div className="flex justify-between items-center pt-2 border-t border-[#dbe7f3] text-sm font-extrabold text-[#1e3a5f]">
              <span className="flex items-center gap-2">
                ยอดชำระสุทธิ
                {user.role === 'admin' && (
                  <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
                    ฟรีสำหรับแอดมิน
                  </span>
                )}
              </span>
              <span data-testid="order-total-payment" className="text-lg text-blue-600">
                {formatCurrency(pricing?.totalPayment ?? 0, currency)}
              </span>
            </div>
          </div>

          <button
            type="submit"
            disabled={submitting || !activeService}
            className="w-full py-3.5 rounded-xl text-sm font-bold text-white nova-gradient-bg shadow-lg shadow-blue-600/20 hover:opacity-95 disabled:opacity-60 transition cursor-pointer"
          >
            {submitting
              ? 'กำลังสร้างคำสั่งซื้อ...'
              : user.role === 'admin'
                ? 'ยืนยันใช้งานบริการฟรี (Admin)'
                : 'ยืนยันสั่งซื้อบริการ'}
          </button>
        </form>

        {/* Service Details Card */}
        {activeService && (
          <div className="lg:col-span-5 space-y-4">
            <div className="nova-card p-6 space-y-4">
              <div className="flex items-start gap-3">
                <PlatformBadge platform={activePlatform} />
                <div className="flex-1 min-w-0">
                  <div className="flex items-center justify-between gap-2">
                    <div className="text-xs font-bold text-blue-600">
                      {activeService.productCode} (#{activeService.id}) · {activePlatform?.name}
                    </div>
                    <span
                      className={`text-[10px] font-bold px-2 py-0.5 rounded ${
                        activeService.addType === 'api'
                          ? 'bg-emerald-50 text-emerald-700'
                          : 'bg-blue-50 text-blue-700'
                      }`}
                    >
                      {activeService.addType === 'api' ? 'API Auto' : 'Manual Fulfillment'}
                    </span>
                  </div>
                  <h2 className="text-base font-extrabold leading-snug mt-0.5">
                    {activeService.name}
                  </h2>
                </div>
              </div>

              <p className="text-xs text-slate-600 leading-relaxed bg-[#f8fbff] p-3.5 rounded-xl border border-[#dbe7f3]">
                {activeService.description}
              </p>

              <div className="grid grid-cols-2 gap-3 text-xs">
                <div className="p-3 rounded-xl bg-[#f4f8fd] border border-[#dbe7f3]">
                  <div className="text-slate-500 flex items-center gap-1">
                    <Zap className="w-3.5 h-3.5 text-sky-500" /> ความเร็วเฉลี่ย
                  </div>
                  <div className="font-bold mt-1">{activeService.speedLabel}</div>
                </div>
                <div className="p-3 rounded-xl bg-[#f4f8fd] border border-[#dbe7f3]">
                  <div className="text-slate-500 flex items-center gap-1">
                    <Clock className="w-3.5 h-3.5 text-blue-500" /> เวลาเริ่มงาน
                  </div>
                  <div className="font-bold mt-1">{activeService.averageTime}</div>
                </div>
                <div className="p-3 rounded-xl bg-[#f4f8fd] border border-[#dbe7f3]">
                  <div className="text-slate-500 flex items-center gap-1">
                    <ShieldCheck className="w-3.5 h-3.5 text-emerald-500" /> การเติมยอด (Refill)
                  </div>
                  <div className="font-bold mt-1">
                    {activeService.refillSupport ? 'รับประกัน 30 วัน' : 'ไม่รองรับ Refill'}
                  </div>
                </div>
                <div className="p-3 rounded-xl bg-[#f4f8fd] border border-[#dbe7f3]">
                  <div className="text-slate-500 flex items-center gap-1">
                    <Layers className="w-3.5 h-3.5 text-amber-500" /> ขั้นต่ำ – สูงสุด
                  </div>
                  <div className="font-bold mt-1">
                    {activeService.minBuy.toLocaleString()} – {activeService.maxBuy.toLocaleString()}
                  </div>
                </div>
              </div>
            </div>

            <div className="nova-card p-5 bg-[#eaf2fb]/60">
              <h3 className="text-xs font-extrabold uppercase tracking-wider text-blue-800 dark:text-sky-300 mb-2">
                คำแนะนำก่อนสั่งซื้อ
              </h3>
              <ul className="text-xs text-slate-600 space-y-1.5 list-disc pl-4">
                <li>ตรวจสอบให้แน่ใจว่าบัญชีหรือโพสต์ตั้งค่าเป็นสาธารณะ (Public)</li>
                <li>ห้ามเปลี่ยนชื่อผู้ใช้ (Username) ระหว่างที่คำสั่งซื้อกำลังดำเนินการ</li>
                <li>คำสั่งซื้อแบบ API จะอัปเดตสถานะจาก Provider จริง และคำสั่งซื้อแบบ Manual จะตรวจสอบโดยผู้ดูแล</li>
              </ul>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
