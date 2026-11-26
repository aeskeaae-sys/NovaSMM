import { useEffect, useState, type FormEvent } from 'react';
import {
  CheckCircle2,
  Edit3,
  FolderPlus,
  KeyRound,
  Plus,
  RefreshCw,
  Server,
  Settings,
  Shield,
  Trash2,
  XCircle,
} from 'lucide-react';
import type {
  Announcement,
  ApiProvider,
  AuditLog,
  BankAccount,
  Category,
  CurrencyCode,
  DepositInvoice,
  Order,
  OrderStatus,
  Platform,
  ProviderLog,
  ProviderServiceItem,
  RankTierConfig,
  Service,
  ServiceType,
  SiteSettings,
  User,
  UserRank,
  Voucher,
} from '../types/smm';
import { formatCurrency } from '../utils/pricing';
import { apiRequest } from '../utils/api';
import { StatusBadge } from '../components/StatusBadge';

interface AdminOverviewData {
  stats: {
    totalUsers: number;
    totalOrders: number;
    pendingOrders: number;
    completedOrders: number;
    totalRevenue: number;
    totalProfit: number;
    totalDeposits: number;
    activeServices: number;
  };
  securityStatus?: {
    sessionSecretConfigured: boolean;
    encryptionKeyConfigured: boolean;
    webhookSecretConfigured: boolean;
    cronSecretConfigured: boolean;
    envSecretSource: 'env' | 'auto-persisted';
  };
  users: User[];
  orders: Order[];
  services: Service[];
  categories: Category[];
  platforms: Platform[];
  invoices: DepositInvoice[];
  vouchers: Voucher[];
  banks: BankAccount[];
  providers: ApiProvider[];
  providerServices: ProviderServiceItem[];
  providerLogs: ProviderLog[];
  announcements: Announcement[];
  auditLogs: AuditLog[];
  rankTiers: RankTierConfig[];
  settings: SiteSettings;
}

export function AdminPage({
  currency,
  onRefreshBootstrap,
}: {
  currency: CurrencyCode;
  onRefreshBootstrap: () => Promise<void>;
}) {
  const [data, setData] = useState<AdminOverviewData | null>(null);
  const [tab, setTab] = useState<
    'overview' | 'orders' | 'invoices' | 'services' | 'users' | 'vouchers' | 'settings' | 'audit'
  >('overview');
  const [msg, setMsg] = useState('');
  const [err, setErr] = useState('');

  // Product / Service Create & Edit Form
  const [editingServiceId, setEditingServiceId] = useState<number | null>(null);
  const [svcProductCode, setSvcProductCode] = useState('');
  const [svcName, setSvcName] = useState('');
  const [svcCategoryId, setSvcCategoryId] = useState<number>(1);
  const [svcPrice, setSvcPrice] = useState<number>(50);
  const [svcOrigPrice, setSvcOrigPrice] = useState<number>(0);
  const [svcMin, setSvcMin] = useState<number>(100);
  const [svcMax, setSvcMax] = useState<number>(50000);
  const [svcType, setSvcType] = useState<ServiceType>('default');
  const [svcSpeed, setSvcSpeed] = useState('5K - 10K / วัน');
  const [svcAvgTime, setSvcAvgTime] = useState('15 นาที');
  const [svcDesc, setSvcDesc] = useState('');
  const [svcRefill, setSvcRefill] = useState(true);
  const [svcAddType, setSvcAddType] = useState<'manual' | 'api'>('manual');
  const [svcProviderId, setSvcProviderId] = useState<number>(0);
  const [svcApiServiceId, setSvcApiServiceId] = useState<string>('');

  // Custom Platform / Category Form
  const [showGroupCreator, setShowGroupCreator] = useState(false);
  const [newPlatName, setNewPlatName] = useState('');
  const [newPlatCode, setNewPlatCode] = useState('');
  const [newPlatColor, setNewPlatColor] = useState('#2563eb');
  const [newCatPlatformId, setNewCatPlatformId] = useState<number>(1);
  const [newCatName, setNewCatName] = useState('');

  // Add Provider Form
  const [provName, setProvName] = useState('');
  const [provUrl, setProvUrl] = useState('');
  const [provKey, setProvKey] = useState('');
  const [provMarkup, setProvMarkup] = useState<number>(30);

  // User wallet adjustment reason & reset token display
  const [adjustReasons, setAdjustReasons] = useState<Record<number, string>>({});
  const [issuedResetToken, setIssuedResetToken] = useState<{
    username: string;
    token: string;
    expiresAt: string;
  } | null>(null);

  // New Bank Account Form
  const [bankName, setBankName] = useState('');
  const [bankShort, setBankShort] = useState('');
  const [bankAccName, setBankAccName] = useState('');
  const [bankAccNum, setBankAccNum] = useState('');
  const [bankPromptPay, setBankPromptPay] = useState('');
  const [bankMinDep, setBankMinDep] = useState<number>(50);

  // New Voucher Form
  const [vCode, setVCode] = useState('');
  const [vValue, setVValue] = useState<number>(50);
  const [vMaxUses, setVMaxUses] = useState<number>(100);

  // Store Branding, Settings & Rank Tiers
  const [siteName, setSiteName] = useState('');
  const [siteTagline, setSiteTagline] = useState('');
  const [siteDescription, setSiteDescription] = useState('');
  const [announcement, setAnnouncement] = useState('');
  const [affPct, setAffPct] = useState<number>(5);
  const [welcomeBonus, setWelcomeBonus] = useState<number>(25);
  const [tiers, setTiers] = useState<RankTierConfig[]>([]);

  async function loadAdmin() {
    try {
      const res = await apiRequest<AdminOverviewData>('/api/admin/overview');
      setData(res);
      setSiteName(res.settings.siteName);
      setSiteTagline(res.settings.siteTagline);
      setSiteDescription(res.settings.siteDescription);
      setAnnouncement(res.settings.announcement);
      setAffPct(res.settings.affiliateCommissionPercent);
      setWelcomeBonus(res.settings.welcomeBonusAmount);
      setTiers(res.rankTiers);
      if (res.categories[0] && !res.categories.some((c) => c.id === svcCategoryId)) {
        setSvcCategoryId(res.categories[0].id);
      }
      if (res.platforms[0] && !res.platforms.some((p) => p.id === newCatPlatformId)) {
        setNewCatPlatformId(res.platforms[0].id);
      }
    } catch (e) {
      setErr(e instanceof Error ? e.message : 'ไม่สามารถโหลดข้อมูลผู้ดูแลระบบได้');
    }
  }

  useEffect(() => {
    loadAdmin();
  }, []);

  async function updateOrderStatus(orderId: number, status: OrderStatus) {
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string }>(`/api/admin/orders/${orderId}/status`, {
        method: 'POST',
        body: JSON.stringify({ status }),
      });
      setMsg(res.message);
      await loadAdmin();
    } catch (e) {
      setErr(e instanceof Error ? e.message : 'อัปเดตไม่สำเร็จ');
    }
  }

  async function approveInvoice(invoiceId: number) {
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string }>(`/api/admin/invoices/${invoiceId}/approve`, {
        method: 'POST',
      });
      setMsg(res.message);
      await loadAdmin();
      await onRefreshBootstrap();
    } catch (e) {
      setErr(e instanceof Error ? e.message : 'อนุมัติไม่สำเร็จ');
    }
  }

  async function rejectInvoice(invoiceId: number) {
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string }>(`/api/admin/invoices/${invoiceId}/reject`, {
        method: 'POST',
        body: JSON.stringify({ reason: 'ไม่พบยอดโอนหรือสลิปไม่ถูกต้อง' }),
      });
      setMsg(res.message);
      await loadAdmin();
    } catch (e) {
      setErr(e instanceof Error ? e.message : 'ปฏิเสธไม่สำเร็จ');
    }
  }

  function resetServiceForm() {
    setEditingServiceId(null);
    setSvcProductCode('');
    setSvcName('');
    setSvcPrice(50);
    setSvcOrigPrice(0);
    setSvcMin(100);
    setSvcMax(50000);
    setSvcType('default');
    setSvcSpeed('5K - 10K / วัน');
    setSvcAvgTime('15 นาที');
    setSvcDesc('');
    setSvcRefill(true);
    setSvcAddType('manual');
    setSvcProviderId(0);
    setSvcApiServiceId('');
  }

  function startEditService(svc: Service) {
    setEditingServiceId(svc.id);
    setSvcProductCode(svc.productCode);
    setSvcName(svc.name);
    setSvcCategoryId(svc.categoryId);
    setSvcPrice(svc.pricePer1k);
    setSvcOrigPrice(svc.originalPricePer1k);
    setSvcMin(svc.minBuy);
    setSvcMax(svc.maxBuy);
    setSvcType(svc.type);
    setSvcSpeed(svc.speedLabel);
    setSvcAvgTime(svc.averageTime);
    setSvcDesc(svc.description);
    setSvcRefill(svc.refillSupport);
    setSvcAddType(svc.addType);
    setSvcProviderId(svc.apiProviderId ?? 0);
    setSvcApiServiceId(svc.apiServiceId ?? '');
    setMsg('');
    setErr('');
  }

  async function saveServiceForm(e: FormEvent) {
    e.preventDefault();
    setMsg('');
    setErr('');
    try {
      const payload = {
        productCode: svcProductCode.trim() || undefined,
        name: svcName,
        categoryId: svcCategoryId,
        pricePer1k: svcPrice,
        originalPricePer1k: svcOrigPrice,
        minBuy: svcMin,
        maxBuy: svcMax,
        type: svcType,
        speedLabel: svcSpeed,
        averageTime: svcAvgTime,
        description: svcDesc,
        refillSupport: svcRefill,
        addType: svcAddType,
        apiProviderId: svcAddType === 'api' && svcProviderId > 0 ? svcProviderId : null,
        apiServiceId: svcAddType === 'api' ? svcApiServiceId : null,
      };
      const res = editingServiceId
        ? await apiRequest<{ message: string }>(`/api/admin/services/${editingServiceId}`, {
            method: 'PUT',
            body: JSON.stringify(payload),
          })
        : await apiRequest<{ message: string }>('/api/admin/services', {
            method: 'POST',
            body: JSON.stringify(payload),
          });
      resetServiceForm();
      setMsg(res.message);
      await loadAdmin();
      await onRefreshBootstrap();
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'บันทึกสินค้า / บริการไม่สำเร็จ');
    }
  }

  async function deleteService(svc: Service) {
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string }>(`/api/admin/services/${svc.id}`, {
        method: 'DELETE',
      });
      if (editingServiceId === svc.id) resetServiceForm();
      setMsg(res.message);
      await loadAdmin();
      await onRefreshBootstrap();
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'ลบสินค้าไม่สำเร็จ');
    }
  }

  async function toggleService(svc: Service) {
    await apiRequest(`/api/admin/services/${svc.id}`, {
      method: 'PUT',
      body: JSON.stringify({ status: !svc.status }),
    });
    await loadAdmin();
    await onRefreshBootstrap();
  }

  async function createPlatform(e: FormEvent) {
    e.preventDefault();
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string; platform: Platform }>('/api/admin/platforms', {
        method: 'POST',
        body: JSON.stringify({
          name: newPlatName,
          code: newPlatCode,
          badgeColor: newPlatColor,
        }),
      });
      setNewPlatName('');
      setNewPlatCode('');
      setNewCatPlatformId(res.platform.id);
      setMsg(res.message);
      await loadAdmin();
      await onRefreshBootstrap();
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'เพิ่มหมวดหลักไม่สำเร็จ');
    }
  }

  async function createCategory(e: FormEvent) {
    e.preventDefault();
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string; category: Category }>('/api/admin/categories', {
        method: 'POST',
        body: JSON.stringify({
          platformId: newCatPlatformId,
          name: newCatName,
        }),
      });
      setNewCatName('');
      setSvcCategoryId(res.category.id);
      setMsg(res.message);
      await loadAdmin();
      await onRefreshBootstrap();
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'เพิ่มหมวดหมู่ย่อยไม่สำเร็จ');
    }
  }

  async function adjustUser(
    userId: number,
    payload: { delta?: number; rank?: UserRank; status?: 'active' | 'locked' }
  ) {
    setMsg('');
    setErr('');
    const reason = (adjustReasons[userId] || 'Admin panel adjustment').trim();
    try {
      const res = await apiRequest<{ message: string }>(`/api/admin/users/${userId}/balance`, {
        method: 'POST',
        body: JSON.stringify({ ...payload, reason }),
      });
      setMsg(res.message);
      await loadAdmin();
      await onRefreshBootstrap();
    } catch (e) {
      setErr(e instanceof Error ? e.message : 'ปรับยอดไม่สำเร็จ');
    }
  }

  async function issueUserResetToken(userId: number) {
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{
        message: string;
        resetToken: string;
        expiresAt: string;
        username: string;
      }>(`/api/admin/users/${userId}/reset-token`, { method: 'POST' });
      setIssuedResetToken({
        username: res.username,
        token: res.resetToken,
        expiresAt: res.expiresAt,
      });
      setMsg(res.message);
      await loadAdmin();
    } catch (e) {
      setErr(e instanceof Error ? e.message : 'ออก Reset Token ไม่สำเร็จ');
    }
  }

  async function createBankAccount(e: FormEvent) {
    e.preventDefault();
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string }>('/api/admin/banks', {
        method: 'POST',
        body: JSON.stringify({
          bankName,
          shortName: bankShort,
          accountName: bankAccName,
          accountNumber: bankAccNum,
          promptPayId: bankPromptPay || bankAccNum,
          minDeposit: bankMinDep,
        }),
      });
      setBankName('');
      setBankShort('');
      setBankAccName('');
      setBankAccNum('');
      setBankPromptPay('');
      setMsg(res.message);
      await loadAdmin();
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'เพิ่มช่องทางรับเงินไม่สำเร็จ');
    }
  }

  async function toggleBankAccount(bank: BankAccount) {
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string }>(`/api/admin/banks/${bank.id}`, {
        method: 'PUT',
        body: JSON.stringify({ status: !bank.status }),
      });
      setMsg(res.message);
      await loadAdmin();
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'อัปเดตบัญชีธนาคารไม่สำเร็จ');
    }
  }

  async function deleteBankAccount(id: number) {
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string }>(`/api/admin/banks/${id}`, {
        method: 'DELETE',
      });
      setMsg(res.message);
      await loadAdmin();
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'ลบบัญชีธนาคารไม่สำเร็จ');
    }
  }

  async function createProvider(e: FormEvent) {
    e.preventDefault();
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string }>('/api/admin/providers', {
        method: 'POST',
        body: JSON.stringify({
          name: provName,
          url: provUrl,
          apiKey: provKey,
          priceIncreasePercent: provMarkup,
        }),
      });
      setProvName('');
      setProvUrl('');
      setProvKey('');
      setMsg(res.message);
      await loadAdmin();
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'เพิ่ม Provider ไม่สำเร็จ');
    }
  }

  async function checkProviderBalance(id: number) {
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string }>(`/api/admin/providers/${id}/balance`, {
        method: 'POST',
      });
      setMsg(res.message);
      await loadAdmin();
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'ตรวจสอบยอดเงินไม่สำเร็จ');
      await loadAdmin();
    }
  }

  async function syncProvider(id: number) {
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string }>(`/api/admin/providers/${id}/sync`, {
        method: 'POST',
      });
      setMsg(res.message);
      await loadAdmin();
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'ซิงค์บริการไม่สำเร็จ');
      await loadAdmin();
    }
  }

  async function createVoucher(e: FormEvent) {
    e.preventDefault();
    setErr('');
    try {
      const res = await apiRequest<{ message: string }>('/api/admin/vouchers', {
        method: 'POST',
        body: JSON.stringify({ code: vCode, value: vValue, maxUses: vMaxUses }),
      });
      setVCode('');
      setMsg(res.message);
      await loadAdmin();
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'สร้างโค้ดไม่สำเร็จ');
    }
  }

  async function deleteVoucher(id: number) {
    await apiRequest(`/api/admin/vouchers/${id}`, { method: 'DELETE' });
    await loadAdmin();
  }

  async function saveSettings(e: FormEvent) {
    e.preventDefault();
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string }>('/api/admin/settings', {
        method: 'POST',
        body: JSON.stringify({
          siteName,
          siteTagline,
          siteDescription,
          announcement,
          affiliateCommissionPercent: affPct,
          welcomeBonusAmount: welcomeBonus,
          rankTiers: tiers,
        }),
      });
      setMsg(res.message);
      await loadAdmin();
      await onRefreshBootstrap();
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'บันทึกการตั้งค่าไม่สำเร็จ');
    }
  }

  if (!data) {
    return (
      <div className="nova-card p-10 text-center text-sm text-slate-500">
        {err || 'กำลังโหลดข้อมูลผู้ดูแลระบบ...'}
      </div>
    );
  }

  const pendingInvoiceCount = data.invoices.filter((i) => i.status === 'pending').length;

  return (
    <div className="space-y-5">
      <div className="nova-card p-5 flex flex-wrap items-center justify-between gap-3 bg-slate-900 text-white border-slate-800">
        <div className="flex items-center gap-2.5">
          <Shield className="w-6 h-6 text-sky-400" />
          <div>
            <h1 className="text-lg font-extrabold">{data.settings.siteName} Admin Control Center</h1>
            <p className="text-xs text-slate-300">
              จัดการคำสั่งซื้อ อนุมัติใบแจ้งหนี้ สินค้า/บริการและรหัส SKU สมาชิก คูปอง API Providers และ Audit Logs
            </p>
          </div>
        </div>
        <button
          type="button"
          onClick={loadAdmin}
          className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 cursor-pointer"
        >
          <RefreshCw className="w-3.5 h-3.5" /> รีเฟรชข้อมูล
        </button>
      </div>

      {msg && (
        <div className="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
          <CheckCircle2 className="w-4 h-4 shrink-0" />
          <span>{msg}</span>
        </div>
      )}
      {err && (
        <div role="alert" className="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold">
          {err}
        </div>
      )}

      {/* Admin Tabs */}
      <div className="flex flex-wrap gap-2">
        {[
          { id: 'overview' as const, label: 'ภาพรวม & API Providers' },
          { id: 'invoices' as const, label: `อนุมัติฝากเงิน (${pendingInvoiceCount} รอตรวจ)` },
          { id: 'orders' as const, label: `คำสั่งซื้อ (${data.orders.length})` },
          { id: 'services' as const, label: `บริการ (${data.services.length})` },
          { id: 'users' as const, label: `สมาชิก (${data.users.length})` },
          { id: 'vouchers' as const, label: 'Vouchers' },
          { id: 'settings' as const, label: 'ตั้งค่า & Rank Tiers' },
          { id: 'audit' as const, label: `Audit Logs (${data.auditLogs.length})` },
        ].map((tItem) => (
          <button
            key={tItem.id}
            type="button"
            onClick={() => setTab(tItem.id)}
            className={`px-3.5 py-2 rounded-xl text-xs font-bold border transition cursor-pointer ${
              tab === tItem.id
                ? 'bg-slate-900 text-white border-slate-900'
                : 'bg-white text-slate-700 border-[#dbe7f3] hover:bg-slate-50'
            }`}
          >
            {tItem.label}
          </button>
        ))}
      </div>

      {tab === 'overview' && (
        <div className="space-y-5">
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="nova-card p-5">
              <div className="text-xs text-slate-500">รายได้รวมคำสั่งซื้อ</div>
              <div className="text-xl font-extrabold text-blue-600 mt-1">
                {formatCurrency(data.stats.totalRevenue, currency)}
              </div>
            </div>
            <div className="nova-card p-5">
              <div className="text-xs text-slate-500">กำไรสุทธิโดยประมาณ</div>
              <div className="text-xl font-extrabold text-emerald-600 mt-1">
                {formatCurrency(data.stats.totalProfit, currency)}
              </div>
            </div>
            <div className="nova-card p-5">
              <div className="text-xs text-slate-500">ออเดอร์รอดำเนินการ</div>
              <div className="text-xl font-extrabold text-amber-600 mt-1">
                {data.stats.pendingOrders}
              </div>
            </div>
            <div className="nova-card p-5">
              <div className="text-xs text-slate-500">ยอดเติมเงินที่อนุมัติแล้ว</div>
              <div className="text-xl font-extrabold text-[#1e3a5f] mt-1">
                {formatCurrency(data.stats.totalDeposits, currency)}
              </div>
            </div>
          </div>

          <div className="grid lg:grid-cols-12 gap-5 items-start">
            <form onSubmit={createProvider} className="lg:col-span-5 nova-card p-5 space-y-3 text-xs">
              <h2 className="text-sm font-extrabold flex items-center gap-1.5">
                <Server className="w-4 h-4 text-blue-600" /> เพิ่ม API Provider ใหม่ (AES-256 Encrypted)
              </h2>
              <div>
                <label className="block font-bold mb-1">ชื่อผู้ให้บริการ</label>
                <input
                  required
                  value={provName}
                  onChange={(e) => setProvName(e.target.value)}
                  placeholder="เช่น Main Thai SMM API"
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
                />
              </div>
              <div>
                <label className="block font-bold mb-1">API Endpoint URL (HTTPS)</label>
                <input
                  required
                  type="url"
                  value={provUrl}
                  onChange={(e) => setProvUrl(e.target.value)}
                  placeholder="https://provider.example.com/api/v2"
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
                />
              </div>
              <div className="grid grid-cols-3 gap-2">
                <div className="col-span-2">
                  <label className="block font-bold mb-1">API Key (ถูกเข้ารหัสก่อนบันทึก)</label>
                  <input
                    required
                    type="password"
                    value={provKey}
                    onChange={(e) => setProvKey(e.target.value)}
                    placeholder="sk_..."
                    className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
                  />
                </div>
                <div>
                  <label className="block font-bold mb-1">บวกกำไร (%)</label>
                  <input
                    type="number"
                    min={0}
                    max={500}
                    value={provMarkup}
                    onChange={(e) => setProvMarkup(Number(e.target.value))}
                    className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
                  />
                </div>
              </div>
              <button
                type="submit"
                className="w-full py-2.5 rounded-xl font-bold text-white nova-gradient-bg cursor-pointer"
              >
                บันทึก API Provider
              </button>
            </form>

            <div className="lg:col-span-7 nova-card p-5 space-y-3">
              <h2 className="text-sm font-extrabold">รายการ API Providers ({data.providers.length})</h2>
              {data.providers.length === 0 ? (
                <div className="text-xs text-slate-400 py-6 text-center">
                  ยังไม่มี External Provider ที่ตั้งค่าไว้ — กรอกแบบฟอร์มด้านซ้ายเพื่อเชื่อมต่อระบบจริง
                </div>
              ) : (
                <div className="space-y-2.5">
                  {data.providers.map((p) => (
                    <div
                      key={p.id}
                      className="p-3.5 rounded-xl border border-[#dbe7f3] bg-[#f8fbff] flex flex-wrap items-center justify-between gap-3 text-xs"
                    >
                      <div className="min-w-0">
                        <div className="font-extrabold text-sm">{p.name}</div>
                        <div className="text-slate-500 font-mono truncate">{p.url}</div>
                        <div className="text-slate-400 mt-0.5">
                          Key: {p.apiKeyMasked} · ยอดเงิน: {p.balance} {p.currency} · +{p.priceIncreasePercent}%
                        </div>
                        {p.lastError && <div className="text-rose-600 mt-1">Error: {p.lastError}</div>}
                      </div>
                      <div className="flex gap-1.5 shrink-0">
                        <button
                          type="button"
                          onClick={() => checkProviderBalance(p.id)}
                          className="px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-700 font-bold cursor-pointer"
                        >
                          เช็กยอดเงิน
                        </button>
                        <button
                          type="button"
                          onClick={() => syncProvider(p.id)}
                          className="px-2.5 py-1.5 rounded-lg bg-blue-600 text-white font-bold cursor-pointer"
                        >
                          ซิงค์บริการ
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>

          {data.providerLogs.length > 0 && (
            <div className="nova-card p-5 space-y-3">
              <h2 className="text-sm font-extrabold">บันทึกการเชื่อมต่อ Provider ล่าสุด</h2>
              <div className="overflow-x-auto max-h-60">
                <table className="w-full text-left text-xs">
                  <thead>
                    <tr className="border-b border-[#dbe7f3] text-slate-500 uppercase text-[11px]">
                      <th className="py-2 px-3">Provider</th>
                      <th className="py-2 px-3">Action</th>
                      <th className="py-2 px-3">ผลลัพธ์</th>
                      <th className="py-2 px-3">รายละเอียด</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {data.providerLogs.map((l) => (
                      <tr key={l.id}>
                        <td className="py-2 px-3 font-bold">{l.providerName}</td>
                        <td className="py-2 px-3 font-mono">{l.action}</td>
                        <td className="py-2 px-3">
                          <span
                            className={`px-2 py-0.5 rounded font-bold ${
                              l.success ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'
                            }`}
                          >
                            {l.success ? `OK (${l.httpStatus ?? 200})` : 'FAILED'}
                          </span>
                        </td>
                        <td className="py-2 px-3 text-slate-500">{l.message}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>
      )}

      {tab === 'invoices' && (
        <div className="nova-card overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead>
                <tr className="border-b border-[#dbe7f3] bg-[#f8fbff] text-slate-500 uppercase text-[11px]">
                  <th className="py-3 px-4">รหัสใบแจ้งหนี้</th>
                  <th className="py-3 px-4">สมาชิก</th>
                  <th className="py-3 px-4">ช่องทาง / หลักฐาน</th>
                  <th className="py-3 px-4">จำนวนสุทธิ</th>
                  <th className="py-3 px-4">สถานะ</th>
                  <th className="py-3 px-4 text-right">การตรวจสอบ</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {data.invoices.map((inv) => (
                  <tr key={inv.id}>
                    <td className="py-3 px-4 font-mono font-bold">{inv.code}</td>
                    <td className="py-3 px-4 font-bold">{inv.username}</td>
                    <td className="py-3 px-4">
                      <div>{inv.channelName}</div>
                      {inv.proofNote && <div className="text-slate-500 text-[11px]">{inv.proofNote}</div>}
                    </td>
                    <td className="py-3 px-4 font-extrabold text-blue-600">
                      {formatCurrency(inv.netAmount, currency)}
                    </td>
                    <td className="py-3 px-4">
                      <span
                        className={`px-2 py-0.5 rounded font-bold ${
                          inv.status === 'paid'
                            ? 'bg-emerald-50 text-emerald-700'
                            : inv.status === 'cancelled'
                              ? 'bg-rose-50 text-rose-700'
                              : 'bg-amber-50 text-amber-700'
                        }`}
                      >
                        {inv.status}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-right space-x-1.5">
                      {inv.status === 'pending' && (
                        <>
                          <button
                            type="button"
                            onClick={() => approveInvoice(inv.id)}
                            className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white font-bold cursor-pointer"
                          >
                            <CheckCircle2 className="w-3.5 h-3.5" /> อนุมัติเติมเงิน
                          </button>
                          <button
                            type="button"
                            onClick={() => rejectInvoice(inv.id)}
                            className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 font-bold cursor-pointer"
                          >
                            <XCircle className="w-3.5 h-3.5" /> ปฏิเสธ
                          </button>
                        </>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {tab === 'orders' && (
        <div className="nova-card overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs">
              <thead>
                <tr className="border-b border-[#dbe7f3] bg-[#f8fbff] text-slate-500 uppercase text-[11px]">
                  <th className="py-3 px-4">ID / ผู้สั่ง</th>
                  <th className="py-3 px-4">บริการ / ลิงก์</th>
                  <th className="py-3 px-4">ยอดชำระ</th>
                  <th className="py-3 px-4">สถานะ</th>
                  <th className="py-3 px-4 text-right">เปลี่ยนสถานะ</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {data.orders.map((o) => (
                  <tr key={o.id}>
                    <td className="py-3 px-4 font-mono">
                      <div className="font-bold">#{o.id}</div>
                      <div className="text-slate-500">{o.username}</div>
                    </td>
                    <td className="py-3 px-4 max-w-xs">
                      <div className="font-bold truncate">{o.serviceName}</div>
                      <div className="text-slate-500 truncate">{o.objectId}</div>
                    </td>
                    <td className="py-3 px-4 font-bold">{formatCurrency(o.totalPayment, currency)}</td>
                    <td className="py-3 px-4">
                      <StatusBadge status={o.status} />
                    </td>
                    <td className="py-3 px-4 text-right">
                      <select
                        aria-label={`Change status for order ${o.id}`}
                        value={o.status}
                        onChange={(e) => updateOrderStatus(o.id, e.target.value as OrderStatus)}
                        className="px-2.5 py-1.5 rounded-lg border border-[#dbe7f3] bg-white text-xs font-semibold"
                      >
                        <option value="Pending">Pending</option>
                        <option value="Processing">Processing</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                        <option value="Partial">Partial</option>
                        <option value="Canceled">Canceled (คืนเงิน)</option>
                        <option value="Refunded">Refunded (คืนเงิน)</option>
                        <option value="Failed">Failed</option>
                      </select>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {tab === 'services' && (
        <div className="space-y-4">
          <div className="flex flex-wrap items-center justify-between gap-2">
            <div className="text-xs text-slate-600 font-semibold">
              คลังสินค้า & บริการของร้านทั้งหมด {data.services.length} รายการ ({data.platforms.length} กลุ่มหลัก · {data.categories.length} หมวดหมู่)
            </div>
            <button
              type="button"
              onClick={() => setShowGroupCreator((v) => !v)}
              className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-[#eaf2fb] text-blue-700 border border-[#dbe7f3] hover:bg-blue-100 cursor-pointer"
            >
              <FolderPlus className="w-3.5 h-3.5" />
              {showGroupCreator ? 'ซ่อนฟอร์มเพิ่มหมวดหมู่' : 'เพิ่มหมวดหลัก / หมวดหมู่สินค้า'}
            </button>
          </div>

          {showGroupCreator && (
            <div className="grid md:grid-cols-2 gap-4 text-xs">
              <form onSubmit={createPlatform} className="nova-card p-4 space-y-2.5">
                <div className="font-extrabold text-sm">เพิ่มกลุ่มหลัก / แพลตฟอร์มใหม่</div>
                <div className="grid grid-cols-3 gap-2">
                  <input
                    required
                    value={newPlatName}
                    onChange={(e) => setNewPlatName(e.target.value)}
                    placeholder="ชื่อ เช่น Shopee / Own Shop"
                    className="col-span-2 px-3 py-2 rounded-xl border border-[#dbe7f3]"
                  />
                  <input
                    required
                    value={newPlatCode}
                    onChange={(e) => setNewPlatCode(e.target.value)}
                    placeholder="ตัวย่อ เช่น SHOP"
                    className="px-3 py-2 rounded-xl border border-[#dbe7f3] uppercase font-mono"
                  />
                </div>
                <div className="flex items-center justify-between gap-2">
                  <label className="flex items-center gap-2 font-semibold">
                    <span>สีป้าย:</span>
                    <input
                      type="color"
                      value={newPlatColor}
                      onChange={(e) => setNewPlatColor(e.target.value)}
                      className="w-8 h-7 rounded border border-[#dbe7f3] cursor-pointer"
                    />
                  </label>
                  <button
                    type="submit"
                    className="px-4 py-2 rounded-xl font-bold text-white nova-gradient-bg cursor-pointer"
                  >
                    บันทึกกลุ่มหลัก
                  </button>
                </div>
              </form>

              <form onSubmit={createCategory} className="nova-card p-4 space-y-2.5">
                <div className="font-extrabold text-sm">เพิ่มหมวดหมู่ย่อยใหม่</div>
                <select
                  aria-label="เลือกกลุ่มหลัก"
                  value={newCatPlatformId}
                  onChange={(e) => setNewCatPlatformId(Number(e.target.value))}
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3] bg-white"
                >
                  {data.platforms.map((p) => (
                    <option key={p.id} value={p.id}>
                      {p.name} ({p.code})
                    </option>
                  ))}
                </select>
                <div className="flex gap-2">
                  <input
                    required
                    value={newCatName}
                    onChange={(e) => setNewCatName(e.target.value)}
                    placeholder="ชื่อหมวดหมู่ เช่น แพ็กเกจดูแลร้านรายเดือน"
                    className="flex-1 px-3 py-2 rounded-xl border border-[#dbe7f3]"
                  />
                  <button
                    type="submit"
                    className="px-4 py-2 rounded-xl font-bold text-white nova-gradient-bg cursor-pointer"
                  >
                    เพิ่มหมวดหมู่
                  </button>
                </div>
              </form>
            </div>
          )}

          <div className="grid lg:grid-cols-12 gap-5 items-start">
            <form onSubmit={saveServiceForm} className="lg:col-span-4 nova-card p-5 space-y-3 text-xs">
              <div className="flex items-center justify-between">
                <h2 className="text-sm font-extrabold flex items-center gap-1.5">
                  {editingServiceId ? (
                    <>
                      <Edit3 className="w-4 h-4 text-amber-600" /> แก้ไขสินค้า #{editingServiceId}
                    </>
                  ) : (
                    <>
                      <Plus className="w-4 h-4 text-blue-600" /> เพิ่มสินค้า / บริการใหม่
                    </>
                  )}
                </h2>
                {editingServiceId && (
                  <button
                    type="button"
                    onClick={resetServiceForm}
                    className="text-[11px] font-bold text-slate-500 hover:text-rose-600 cursor-pointer"
                  >
                    ยกเลิกการแก้ไข
                  </button>
                )}
              </div>

              <div>
                <label className="block font-bold mb-1">รหัสสินค้า (SKU Code)</label>
                <input
                  value={svcProductCode}
                  onChange={(e) => setSvcProductCode(e.target.value.toUpperCase())}
                  placeholder="เช่น SHOP-001 (เว้นว่างสร้าง PRD-XXX อัตโนมัติ)"
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3] font-mono uppercase"
                />
              </div>

              <div>
                <label className="block font-bold mb-1">ชื่อสินค้า / บริการ</label>
                <input
                  required
                  value={svcName}
                  onChange={(e) => setSvcName(e.target.value)}
                  placeholder="เช่น LINE OA เพิ่มเพื่อนคนไทยจริง"
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
                />
              </div>

              <div>
                <label className="block font-bold mb-1">หมวดหมู่</label>
                <select
                  value={svcCategoryId}
                  onChange={(e) => setSvcCategoryId(Number(e.target.value))}
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3] bg-white"
                >
                  {data.categories.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.name}
                    </option>
                  ))}
                </select>
              </div>

              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="block font-bold mb-1">ราคาขาย/1K (฿)</label>
                  <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    required
                    value={svcPrice}
                    onChange={(e) => setSvcPrice(Number(e.target.value))}
                    className="w-full px-2.5 py-2 rounded-xl border border-[#dbe7f3]"
                  />
                </div>
                <div>
                  <label className="block font-bold mb-1">ต้นทุน/1K (฿)</label>
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    value={svcOrigPrice}
                    onChange={(e) => setSvcOrigPrice(Number(e.target.value))}
                    className="w-full px-2.5 py-2 rounded-xl border border-[#dbe7f3]"
                  />
                </div>
              </div>

              <div className="grid grid-cols-3 gap-2">
                <div>
                  <label className="block font-bold mb-1">ขั้นต่ำ</label>
                  <input
                    type="number"
                    min={1}
                    required
                    value={svcMin}
                    onChange={(e) => setSvcMin(Number(e.target.value))}
                    className="w-full px-2.5 py-2 rounded-xl border border-[#dbe7f3]"
                  />
                </div>
                <div>
                  <label className="block font-bold mb-1">สูงสุด</label>
                  <input
                    type="number"
                    min={1}
                    required
                    value={svcMax}
                    onChange={(e) => setSvcMax(Number(e.target.value))}
                    className="w-full px-2.5 py-2 rounded-xl border border-[#dbe7f3]"
                  />
                </div>
                <div>
                  <label className="block font-bold mb-1">ชนิดฟอร์ม</label>
                  <select
                    value={svcType}
                    onChange={(e) => setSvcType(e.target.value as ServiceType)}
                    className="w-full px-2 py-2 rounded-xl border border-[#dbe7f3] bg-white"
                  >
                    <option value="default">มาตรฐาน</option>
                    <option value="custom_comments">คอมเมนต์</option>
                    <option value="reactions">Reaction</option>
                    <option value="vip_like">VIP</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="block font-bold mb-1">ความเร็ว</label>
                  <input
                    value={svcSpeed}
                    onChange={(e) => setSvcSpeed(e.target.value)}
                    placeholder="เช่น 5K - 10K / วัน"
                    className="w-full px-2.5 py-2 rounded-xl border border-[#dbe7f3]"
                  />
                </div>
                <div>
                  <label className="block font-bold mb-1">เวลาเริ่มงาน</label>
                  <input
                    value={svcAvgTime}
                    onChange={(e) => setSvcAvgTime(e.target.value)}
                    placeholder="เช่น 15 นาที"
                    className="w-full px-2.5 py-2 rounded-xl border border-[#dbe7f3]"
                  />
                </div>
              </div>

              <div>
                <label className="block font-bold mb-1">รายละเอียดสินค้า</label>
                <textarea
                  rows={2}
                  value={svcDesc}
                  onChange={(e) => setSvcDesc(e.target.value)}
                  placeholder="อธิบายจุดเด่นและเงื่อนไขการรับประกัน..."
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
                />
              </div>

              <div className="space-y-2 pt-1 border-t border-slate-100">
                <label className="block font-bold">รูปแบบการส่งงาน (Fulfillment)</label>
                <select
                  value={svcAddType}
                  onChange={(e) => setSvcAddType(e.target.value as 'manual' | 'api')}
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3] bg-white"
                >
                  <option value="manual">Manual (ดำเนินการเอง)</option>
                  <option value="api">API Auto (ส่งต่อ Provider อัตโนมัติ)</option>
                </select>

                {svcAddType === 'api' && (
                  <div className="space-y-2 p-2.5 rounded-xl bg-[#f8fbff] border border-[#dbe7f3]">
                    <div>
                      <label className="block font-bold mb-1">API Provider</label>
                      <select
                        value={svcProviderId}
                        onChange={(e) => setSvcProviderId(Number(e.target.value))}
                        className="w-full px-2.5 py-2 rounded-lg border border-[#dbe7f3] bg-white"
                      >
                        <option value={0}>-- เลือก Provider --</option>
                        {data.providers.map((p) => (
                          <option key={p.id} value={p.id}>
                            {p.name}
                          </option>
                        ))}
                      </select>
                    </div>
                    <div>
                      <label className="block font-bold mb-1">Provider Service ID</label>
                      <input
                        list="synced-provider-services"
                        required
                        value={svcApiServiceId}
                        onChange={(e) => setSvcApiServiceId(e.target.value)}
                        placeholder="เช่น 105"
                        className="w-full px-2.5 py-2 rounded-lg border border-[#dbe7f3] bg-white font-mono"
                      />
                      <datalist id="synced-provider-services">
                        {data.providerServices
                          .filter((ps) => svcProviderId === 0 || ps.providerId === svcProviderId)
                          .map((ps) => (
                            <option key={ps.id} value={ps.externalServiceId}>
                              #{ps.externalServiceId} — {ps.name} ({ps.rate}/1K)
                            </option>
                          ))}
                      </datalist>
                    </div>
                  </div>
                )}
              </div>

              <label className="flex items-center gap-2 font-semibold cursor-pointer">
                <input
                  type="checkbox"
                  checked={svcRefill}
                  onChange={(e) => setSvcRefill(e.target.checked)}
                />
                <span>รองรับการเติมยอด (Refill 30D)</span>
              </label>

              <button
                type="submit"
                className="w-full py-2.5 rounded-xl font-bold text-white nova-gradient-bg cursor-pointer"
              >
                {editingServiceId ? 'บันทึกการแก้ไขสินค้า' : 'เพิ่มสินค้า / บริการ'}
              </button>
            </form>

            <div className="lg:col-span-8 nova-card overflow-hidden">
              <div className="overflow-x-auto max-h-[620px]">
                <table className="w-full text-left text-xs">
                  <thead>
                    <tr className="border-b border-[#dbe7f3] bg-[#f8fbff] text-slate-500 uppercase text-[11px]">
                      <th className="py-3 px-4">รหัส SKU</th>
                      <th className="py-3 px-4">ชื่อสินค้า / หมวดหมู่</th>
                      <th className="py-3 px-4">การส่งงาน</th>
                      <th className="py-3 px-4">ราคา/1K</th>
                      <th className="py-3 px-4 text-right">จัดการ</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {data.services.map((s) => {
                      const cat = data.categories.find((c) => c.id === s.categoryId);
                      return (
                        <tr
                          key={s.id}
                          className={editingServiceId === s.id ? 'bg-amber-50/70' : 'hover:bg-[#f8fbff]'}
                        >
                          <td className="py-2.5 px-4 font-mono whitespace-nowrap">
                            <div className="font-bold text-blue-700">{s.productCode}</div>
                            <div className="text-[10px] text-slate-400">#{s.id}</div>
                          </td>
                          <td className="py-2.5 px-4">
                            <div className="font-semibold">{s.name}</div>
                            <div className="text-[11px] text-slate-500">{cat?.name || '-'}</div>
                          </td>
                          <td className="py-2.5 px-4 whitespace-nowrap">
                            <span
                              className={`px-2 py-0.5 rounded font-bold ${
                                s.addType === 'api'
                                  ? 'bg-emerald-50 text-emerald-700'
                                  : 'bg-blue-50 text-blue-700'
                              }`}
                            >
                              {s.addType === 'api'
                                ? `API (#${s.apiServiceId || '-'})`
                                : 'Manual'}
                            </span>
                          </td>
                          <td className="py-2.5 px-4 font-bold text-blue-600 whitespace-nowrap">
                            {formatCurrency(s.pricePer1k, currency)}
                          </td>
                          <td className="py-2.5 px-4 text-right whitespace-nowrap space-x-1">
                            <button
                              type="button"
                              aria-label={`Edit ${s.productCode}`}
                              onClick={() => startEditService(s)}
                              className="px-2 py-1 rounded-lg bg-amber-50 text-amber-800 font-bold hover:bg-amber-100 cursor-pointer"
                            >
                              แก้ไข
                            </button>
                            <button
                              type="button"
                              onClick={() => toggleService(s)}
                              className={`px-2 py-1 rounded-lg font-bold cursor-pointer ${
                                s.status
                                  ? 'bg-emerald-50 text-emerald-700'
                                  : 'bg-slate-100 text-slate-500'
                              }`}
                            >
                              {s.status ? 'เปิด' : 'ปิด'}
                            </button>
                            <button
                              type="button"
                              aria-label={`Delete ${s.productCode}`}
                              onClick={() => deleteService(s)}
                              className="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg align-middle cursor-pointer"
                            >
                              <Trash2 className="w-3.5 h-3.5" />
                            </button>
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      )}

      {tab === 'users' && (
        <div className="space-y-4">
          {issuedResetToken && (
            <div className="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-xs space-y-1.5">
              <div className="font-extrabold text-amber-900">
                Reset Token สำหรับ {issuedResetToken.username} (ส่งให้ผู้ใช้กรอกในหน้าตั้งรหัสผ่านใหม่)
              </div>
              <div className="flex flex-wrap items-center gap-2">
                <code className="px-3 py-1.5 rounded-lg bg-white border border-amber-200 font-mono text-slate-900 break-all">
                  {issuedResetToken.token}
                </code>
                <button
                  type="button"
                  onClick={() => navigator.clipboard?.writeText(issuedResetToken.token)}
                  className="px-3 py-1.5 rounded-lg bg-amber-600 text-white font-bold cursor-pointer"
                >
                  คัดลอกโทเคน
                </button>
              </div>
            </div>
          )}
          <div className="nova-card overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead>
                  <tr className="border-b border-[#dbe7f3] bg-[#f8fbff] text-slate-500 uppercase text-[11px]">
                    <th className="py-3 px-4">สมาชิก</th>
                    <th className="py-3 px-4">ระดับ / สถานะ</th>
                    <th className="py-3 px-4">ยอดเงินคงเหลือ</th>
                    <th className="py-3 px-4">เหตุผลในการปรับยอด</th>
                    <th className="py-3 px-4 text-right">จัดการบัญชี</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {data.users.map((u) => (
                    <tr key={u.id}>
                      <td className="py-3 px-4">
                        <div className="font-bold">
                          {u.username}{' '}
                          {u.role === 'admin' && (
                            <span className="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-slate-900 text-white">
                              ADMIN
                            </span>
                          )}
                        </div>
                        <div className="text-slate-500">{u.email}</div>
                      </td>
                      <td className="py-3 px-4 space-x-1.5">
                        <select
                          aria-label={`Rank for ${u.username}`}
                          value={u.rank}
                          onChange={(e) => adjustUser(u.id, { rank: e.target.value as UserRank })}
                          className="px-2 py-1 rounded-lg border border-[#dbe7f3] bg-white font-bold uppercase"
                        >
                          <option value="member">member</option>
                          <option value="silver">silver</option>
                          <option value="gold">gold</option>
                          <option value="platinum">platinum</option>
                          <option value="agency">agency</option>
                        </select>
                        <button
                          type="button"
                          onClick={() =>
                            adjustUser(u.id, { status: u.status === 'active' ? 'locked' : 'active' })
                          }
                          className={`px-2 py-1 rounded-lg font-bold cursor-pointer ${
                            u.status === 'active'
                              ? 'bg-emerald-50 text-emerald-700'
                              : 'bg-rose-50 text-rose-700'
                          }`}
                        >
                          {u.status}
                        </button>
                      </td>
                      <td className="py-3 px-4 font-extrabold text-blue-600">
                        {formatCurrency(u.balance, currency)}
                      </td>
                      <td className="py-3 px-4">
                        <input
                          type="text"
                          placeholder="เหตุผล (Audit Log)"
                          value={adjustReasons[u.id] ?? ''}
                          onChange={(e) =>
                            setAdjustReasons((prev) => ({ ...prev, [u.id]: e.target.value }))
                          }
                          className="w-40 px-2 py-1 rounded-lg border border-[#dbe7f3]"
                        />
                      </td>
                      <td className="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                        <button
                          type="button"
                          onClick={() => adjustUser(u.id, { delta: 100 })}
                          className="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold cursor-pointer"
                        >
                          +฿100
                        </button>
                        <button
                          type="button"
                          onClick={() => adjustUser(u.id, { delta: -100 })}
                          className="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-bold cursor-pointer"
                        >
                          -฿100
                        </button>
                        <button
                          type="button"
                          onClick={() => issueUserResetToken(u.id)}
                          className="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 font-bold cursor-pointer"
                        >
                          Reset Token
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {tab === 'vouchers' && (
        <div className="nova-card p-5 space-y-4 max-w-3xl">
          <h2 className="text-sm font-extrabold">จัดการโค้ดของขวัญ (Vouchers)</h2>
          <form onSubmit={createVoucher} className="grid sm:grid-cols-4 gap-2 text-xs">
            <input
              required
              value={vCode}
              onChange={(e) => setVCode(e.target.value)}
              placeholder="รหัสโค้ด เช่น PROMO50"
              className="sm:col-span-2 px-3 py-2 rounded-xl border border-[#dbe7f3] uppercase font-mono"
            />
            <input
              type="number"
              required
              min={1}
              value={vValue}
              onChange={(e) => setVValue(Number(e.target.value))}
              placeholder="มูลค่า (THB)"
              className="px-3 py-2 rounded-xl border border-[#dbe7f3]"
            />
            <div className="flex gap-2">
              <input
                type="number"
                required
                min={1}
                value={vMaxUses}
                onChange={(e) => setVMaxUses(Number(e.target.value))}
                title="จำนวนสิทธิ์สูงสุด"
                className="w-20 px-2.5 py-2 rounded-xl border border-[#dbe7f3]"
              />
              <button
                type="submit"
                className="flex-1 px-4 py-2 rounded-xl font-bold text-white nova-gradient-bg cursor-pointer"
              >
                สร้าง
              </button>
            </div>
          </form>
          <div className="space-y-2 text-xs">
            {data.vouchers.map((v) => (
              <div
                key={v.id}
                className="p-3 rounded-xl border border-[#dbe7f3] flex items-center justify-between"
              >
                <div>
                  <span className="font-mono font-extrabold text-blue-700">{v.code}</span>
                  <span className="ml-2 text-slate-500">
                    มูลค่า {formatCurrency(v.value, currency)} · ใช้แล้ว {v.usedCount}/{v.maxUses}
                    {v.allowedRanks.length > 0 && ` · เฉพาะ ${v.allowedRanks.join(', ')}`}
                  </span>
                </div>
                <button
                  type="button"
                  aria-label={`Delete voucher ${v.code}`}
                  onClick={() => deleteVoucher(v.id)}
                  className="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg cursor-pointer"
                >
                  <Trash2 className="w-3.5 h-3.5" />
                </button>
              </div>
            ))}
          </div>
        </div>
      )}

      {tab === 'settings' && (
        <div className="space-y-6 max-w-4xl">
          <form onSubmit={saveSettings} className="nova-card p-6 space-y-5 text-xs">
            <h2 className="text-base font-extrabold flex items-center gap-2">
              <Settings className="w-4 h-4 text-blue-600" /> ตั้งค่าชื่อร้านค้า แบรนด์ และส่วนลด Rank Tiers
            </h2>

            <div className="grid sm:grid-cols-2 gap-4">
              <div>
                <label htmlFor="admin-site-name" className="block font-bold mb-1">
                  ชื่อร้านค้า / เว็บไซต์ (Store Name)
                </label>
                <input
                  id="admin-site-name"
                  required
                  value={siteName}
                  onChange={(e) => setSiteName(e.target.value)}
                  placeholder="เช่น AuraBoost Store"
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
                />
              </div>
              <div>
                <label htmlFor="admin-site-tagline" className="block font-bold mb-1">
                  สโลแกนร้าน (Tagline)
                </label>
                <input
                  id="admin-site-tagline"
                  value={siteTagline}
                  onChange={(e) => setSiteTagline(e.target.value)}
                  placeholder="ศูนย์รวมบริการและสินค้าดิจิทัล"
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
                />
              </div>
            </div>

            <div>
              <label htmlFor="admin-site-desc" className="block font-bold mb-1">
                คำอธิบายร้าน (SEO Description)
              </label>
              <input
                id="admin-site-desc"
                value={siteDescription}
                onChange={(e) => setSiteDescription(e.target.value)}
                className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
              />
            </div>

            <div>
              <label className="block font-bold mb-1">ข้อความประกาศด้านบน (Announcement)</label>
              <textarea
                rows={2}
                value={announcement}
                onChange={(e) => setAnnouncement(e.target.value)}
                className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
              />
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block font-bold mb-1">ค่าคอมมิชชันแนะนำเพื่อน (%)</label>
                <input
                  type="number"
                  min={0}
                  max={50}
                  value={affPct}
                  onChange={(e) => setAffPct(Number(e.target.value))}
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
                />
              </div>
              <div>
                <label className="block font-bold mb-1">โบนัสต้อนรับสมาชิกใหม่ (THB)</label>
                <input
                  type="number"
                  min={0}
                  max={5000}
                  value={welcomeBonus}
                  onChange={(e) => setWelcomeBonus(Number(e.target.value))}
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
                />
              </div>
            </div>

            <div className="space-y-2">
              <div className="font-bold">กำหนดเปอร์เซ็นต์ส่วนลดตามระดับสมาชิก (Rank Tiers)</div>
              <div className="grid sm:grid-cols-5 gap-2">
                {tiers.map((tier, idx) => (
                  <div key={tier.rank} className="p-2.5 rounded-xl border border-[#dbe7f3] bg-[#f8fbff] space-y-1.5">
                    <div className="font-extrabold uppercase">{tier.label}</div>
                    <label className="block text-[10px] text-slate-500">ส่วนลด (%)</label>
                    <input
                      type="number"
                      min={0}
                      max={80}
                      value={tier.discountPercent}
                      onChange={(e) => {
                        const next = [...tiers];
                        next[idx] = { ...tier, discountPercent: Number(e.target.value) };
                        setTiers(next);
                      }}
                      className="w-full px-2 py-1 rounded border border-[#dbe7f3]"
                    />
                  </div>
                ))}
              </div>
            </div>

            <button
              type="submit"
              className="px-5 py-2.5 rounded-xl font-bold text-white nova-gradient-bg cursor-pointer"
            >
              บันทึกการตั้งค่า
            </button>
          </form>

          {data.securityStatus && (
            <div className="nova-card p-6 space-y-3 text-xs">
              <div className="flex flex-wrap items-center justify-between gap-2">
                <h2 className="text-base font-extrabold flex items-center gap-2">
                  <KeyRound className="w-4 h-4 text-blue-600" /> สถานะกุญแจความปลอดภัยเซิร์ฟเวอร์ (Secrets)
                </h2>
                <span className="px-2.5 py-1 rounded-lg bg-slate-100 font-mono text-[11px] font-bold text-slate-700">
                  Source: {data.securityStatus.envSecretSource === 'env' ? 'Environment Variables (.env / Secrets)' : 'Auto-Persisted Server Keys'}
                </span>
              </div>
              <p className="text-slate-500">
                ระบบตรวจเช็กความพร้อมของ Secret Keys ฝั่งเซิร์ฟเวอร์โดยไม่เปิดเผยค่าจริงผ่านหน้าเว็บ หากต้องการสร้างชุดใหม่รันคำสั่ง{' '}
                <code className="px-1.5 py-0.5 rounded bg-slate-100 font-mono">npm run secrets:generate</code>
              </p>
              <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                {[
                  { key: 'SESSION_SECRET', ok: data.securityStatus.sessionSecretConfigured },
                  { key: 'API_ENCRYPTION_KEY', ok: data.securityStatus.encryptionKeyConfigured },
                  { key: 'PAYMENT_WEBHOOK_SECRET', ok: data.securityStatus.webhookSecretConfigured },
                  { key: 'CRON_SECRET', ok: data.securityStatus.cronSecretConfigured },
                ].map((item) => (
                  <div
                    key={item.key}
                    className="p-3 rounded-xl border border-[#dbe7f3] bg-[#f8fbff] flex items-center justify-between gap-2"
                  >
                    <span className="font-mono font-bold text-[11px] truncate">{item.key}</span>
                    <span
                      className={`px-2 py-0.5 rounded text-[10px] font-bold shrink-0 ${
                        item.ok ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'
                      }`}
                    >
                      {item.ok ? 'ACTIVE' : 'MISSING'}
                    </span>
                  </div>
                ))}
              </div>
            </div>
          )}

          <div className="nova-card p-6 space-y-4 text-xs">
            <h2 className="text-base font-extrabold">จัดการช่องทางรับเงิน (บัญชีธนาคาร & PromptPay)</h2>
            <form onSubmit={createBankAccount} className="grid sm:grid-cols-3 gap-3">
              <input
                required
                value={bankName}
                onChange={(e) => setBankName(e.target.value)}
                placeholder="ชื่อธนาคาร เช่น ธนาคารกสิกรไทย"
                className="px-3 py-2 rounded-xl border border-[#dbe7f3]"
              />
              <input
                required
                value={bankShort}
                onChange={(e) => setBankShort(e.target.value)}
                placeholder="ตัวย่อ เช่น KBANK"
                className="px-3 py-2 rounded-xl border border-[#dbe7f3] uppercase"
              />
              <input
                required
                value={bankAccName}
                onChange={(e) => setBankAccName(e.target.value)}
                placeholder="ชื่อบัญชีผู้รับเงิน"
                className="px-3 py-2 rounded-xl border border-[#dbe7f3]"
              />
              <input
                required
                value={bankAccNum}
                onChange={(e) => setBankAccNum(e.target.value)}
                placeholder="เลขที่บัญชี"
                className="px-3 py-2 rounded-xl border border-[#dbe7f3] font-mono"
              />
              <input
                value={bankPromptPay}
                onChange={(e) => setBankPromptPay(e.target.value)}
                placeholder="PromptPay ID (ไม่บังคับ)"
                className="px-3 py-2 rounded-xl border border-[#dbe7f3] font-mono"
              />
              <div className="flex gap-2">
                <input
                  type="number"
                  min={1}
                  required
                  value={bankMinDep}
                  onChange={(e) => setBankMinDep(Number(e.target.value))}
                  title="ขั้นต่ำ (THB)"
                  className="w-24 px-2.5 py-2 rounded-xl border border-[#dbe7f3]"
                />
                <button
                  type="submit"
                  className="flex-1 px-4 py-2 rounded-xl font-bold text-white nova-gradient-bg cursor-pointer"
                >
                  เพิ่มบัญชี
                </button>
              </div>
            </form>

            <div className="space-y-2">
              {data.banks.length === 0 ? (
                <div className="text-slate-400 py-4 text-center">
                  ยังไม่มีบัญชีรับเงิน — เพิ่มบัญชีธนาคารจริงของคุณด้านบนเพื่อให้ลูกค้าแจ้งโอนเงินได้
                </div>
              ) : (
                data.banks.map((b) => (
                  <div
                    key={b.id}
                    className="p-3 rounded-xl border border-[#dbe7f3] bg-[#f8fbff] flex flex-wrap items-center justify-between gap-2"
                  >
                    <div>
                      <span className="font-extrabold text-blue-700 mr-2">[{b.shortName}]</span>
                      <span className="font-bold">{b.bankName}</span>
                      <span className="mx-2 text-slate-400">·</span>
                      <span className="font-mono font-bold">{b.accountNumber}</span>
                      <span className="ml-2 text-slate-500">({b.accountName}) · ขั้นต่ำ ฿{b.minDeposit}</span>
                    </div>
                    <div className="flex items-center gap-1.5">
                      <button
                        type="button"
                        onClick={() => toggleBankAccount(b)}
                        className={`px-2.5 py-1 rounded-lg font-bold cursor-pointer ${
                          b.status ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'
                        }`}
                      >
                        {b.status ? 'เปิดรับโอน' : 'ปิดชั่วคราว'}
                      </button>
                      <button
                        type="button"
                        aria-label={`Delete bank ${b.bankName}`}
                        onClick={() => deleteBankAccount(b.id)}
                        className="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg cursor-pointer"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      )}

      {tab === 'audit' && (
        <div className="nova-card overflow-hidden">
          <div className="overflow-x-auto max-h-[540px]">
            <table className="w-full text-left text-xs">
              <thead>
                <tr className="border-b border-[#dbe7f3] bg-[#f8fbff] text-slate-500 uppercase text-[11px]">
                  <th className="py-3 px-4">เวลา</th>
                  <th className="py-3 px-4">ผู้กระทำ</th>
                  <th className="py-3 px-4">Action</th>
                  <th className="py-3 px-4">เป้าหมาย</th>
                  <th className="py-3 px-4">เหตุผล / รายละเอียด</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {data.auditLogs.map((log) => (
                  <tr key={log.id}>
                    <td className="py-2.5 px-4 text-slate-500 whitespace-nowrap">
                      {new Date(log.createdAt).toLocaleString()}
                    </td>
                    <td className="py-2.5 px-4 font-bold">{log.actorUsername}</td>
                    <td className="py-2.5 px-4 font-mono text-blue-700">{log.action}</td>
                    <td className="py-2.5 px-4 font-mono">
                      {log.targetType}:{log.targetId}
                    </td>
                    <td className="py-2.5 px-4 text-slate-600">
                      {log.reason || log.metadata}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}
