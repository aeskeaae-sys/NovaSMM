import { useState } from 'react';
import {
  ArrowRight,
  BarChart3,
  Check,
  Code2,
  Headphones,
  Menu,
  ShieldCheck,
  Sparkles,
  Wallet,
  X,
  Zap,
} from 'lucide-react';
import type { CurrencyCode, LocaleCode, Platform, Service, SiteSettings } from '../types/smm';
import { formatCurrency } from '../utils/pricing';
import { t } from '../i18n/translations';

interface LandingPageProps {
  locale: LocaleCode;
  currency: CurrencyCode;
  theme: 'light' | 'dark';
  needsAdminSetup: boolean;
  settings: SiteSettings;
  platforms: Platform[];
  services: Service[];
  publicStats: {
    totalServices: number;
    totalOrdersCompleted: number;
    ordersToday: number;
    totalUsers: number;
    uptimePercent: number;
  };
  isAuthenticated: boolean;
  onChangeLocale: (l: LocaleCode) => void;
  onToggleTheme: () => void;
  onNavigate: (view: string) => void;
}

export function LandingPage({
  locale,
  currency,
  theme,
  needsAdminSetup,
  settings,
  platforms,
  services,
  publicStats,
  isAuthenticated,
  onChangeLocale,
  onToggleTheme,
  onNavigate,
}: LandingPageProps) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const faqs = [
    {
      q: `${settings.siteName} ใช้งานอย่างไรและเริ่มงานเร็วแค่ไหน?`,
      a: 'หลังสมัครสมาชิกและเติมเงิน คุณสามารถเลือกแพลตฟอร์มหรือหมวดสินค้า ใส่ลิงก์เป้าหมายและจำนวน ระบบจะส่งคำสั่งซื้ออัตโนมัติภายในไม่กี่วินาที',
    },
    {
      q: 'ปลอดภัยต่อบัญชีโซเชียลมีเดียหรือไม่?',
      a: 'ปลอดภัย 100% เพราะเราไม่เคยขอรหัสผ่านบัญชีของคุณ ใช้เพียงลิงก์โพสต์หรือชื่อผู้ใช้สาธารณะเท่านั้น',
    },
    {
      q: 'มีระบบ Refill รับประกันยอดลดหรือไม่?',
      a: 'บริการที่มีป้าย Refill 30D รองรับการกดเติมยอดชดเชยฟรีในหน้าประวัติคำสั่งซื้อตลอดระยะเวลารับประกัน',
    },
    {
      q: 'สามารถเชื่อมต่อ API เพื่อเปิดเว็บตัวแทนจำหน่ายได้ไหม?',
      a: 'รองรับเต็มรูปแบบผ่านมาตรฐาน SMM API v2 ที่ Endpoint /api/v2 พร้อมเอกสารและเครื่องมือทดสอบในตัว',
    },
  ];

  return (
    <div className="min-h-screen bg-[#f4f8fd] text-[#1e3a5f]">
      {/* Header */}
      <header className="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-[#dbe7f3]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 h-18 flex items-center justify-between gap-4">
          <a href="#top" className="flex items-center gap-2.5">
            <img
              src="/assets/images/brand-logos/nova-icon.svg"
              alt={settings.siteName}
              className="w-9 h-9"
            />
            <div className="flex flex-col leading-tight">
              <span className="text-xl font-extrabold nova-gradient-text tracking-tight">
                {settings.siteName}
              </span>
              <span className="text-[11px] font-medium text-slate-500">
                {settings.siteTagline || t(locale, 'brandTagline')}
              </span>
            </div>
          </a>

          <nav className="hidden md:flex items-center gap-7 text-sm font-medium text-slate-600">
            <button
              type="button"
              onClick={() => onNavigate('catalog')}
              className="hover:text-blue-600 transition-colors cursor-pointer"
            >
              {t(locale, 'navServices')}
            </button>
            <a href="#how" className="hover:text-blue-600 transition-colors">
              {t(locale, 'navHow')}
            </a>
            <button
              type="button"
              onClick={() => onNavigate('api-docs')}
              className="hover:text-blue-600 transition-colors cursor-pointer"
            >
              {t(locale, 'navApi')}
            </button>
            <a href="#faq" className="hover:text-blue-600 transition-colors">
              {t(locale, 'navFaq')}
            </a>
          </nav>

          <div className="hidden md:flex items-center gap-2.5">
            <button
              type="button"
              aria-label="Toggle color theme"
              onClick={onToggleTheme}
              className="px-2.5 py-2 rounded-lg text-xs font-semibold bg-[#eaf2fb] border border-[#dbe7f3] text-[#1e3a5f] cursor-pointer"
            >
              {theme === 'dark' ? '☀️' : '🌙'}
            </button>
            <label htmlFor="landing-locale" className="sr-only">
              Language
            </label>
            <select
              id="landing-locale"
              value={locale}
              onChange={(e) => onChangeLocale(e.target.value as LocaleCode)}
              className="text-xs font-semibold bg-[#eaf2fb] border border-[#dbe7f3] text-[#1e3a5f] rounded-lg px-2.5 py-2"
            >
              <option value="th">TH ไทย</option>
              <option value="en">EN English</option>
              <option value="vi">VI Tiếng Việt</option>
            </select>

            {isAuthenticated ? (
              <button
                type="button"
                onClick={() => onNavigate('dashboard')}
                className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white nova-gradient-bg shadow-sm hover:opacity-95 transition cursor-pointer"
              >
                {t(locale, 'dashboard')} <ArrowRight className="w-4 h-4" />
              </button>
            ) : (
              <>
                <button
                  type="button"
                  onClick={() => onNavigate('login')}
                  className="px-4 py-2 rounded-xl text-sm font-semibold text-[#1e3a5f] bg-white border border-[#dbe7f3] hover:bg-slate-50 transition cursor-pointer"
                >
                  {t(locale, 'login')}
                </button>
                <button
                  type="button"
                  onClick={() => onNavigate('register')}
                  className="px-4 py-2 rounded-xl text-sm font-semibold text-white nova-gradient-bg shadow-sm hover:opacity-95 transition cursor-pointer"
                >
                  {t(locale, 'register')}
                </button>
              </>
            )}
          </div>

          <button
            type="button"
            aria-label="Toggle navigation menu"
            onClick={() => setMobileMenuOpen((v) => !v)}
            className="md:hidden p-2 rounded-lg bg-white border border-[#dbe7f3] text-[#1e3a5f]"
          >
            {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
          </button>
        </div>

        {mobileMenuOpen && (
          <div className="md:hidden border-t border-[#dbe7f3] bg-white px-4 py-4 space-y-3">
            <div className="flex flex-col gap-2 text-sm font-medium">
              <button
                type="button"
                onClick={() => {
                  setMobileMenuOpen(false);
                  onNavigate('catalog');
                }}
                className="text-left py-2"
              >
                {t(locale, 'navServices')}
              </button>
              <button
                type="button"
                onClick={() => {
                  setMobileMenuOpen(false);
                  onNavigate('api-docs');
                }}
                className="text-left py-2"
              >
                {t(locale, 'navApi')}
              </button>
            </div>
            <div className="flex gap-2 pt-2">
              <button
                type="button"
                onClick={() => onNavigate('login')}
                className="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-[#dbe7f3]"
              >
                {t(locale, 'login')}
              </button>
              <button
                type="button"
                onClick={() => onNavigate('register')}
                className="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white nova-gradient-bg"
              >
                {t(locale, 'register')}
              </button>
            </div>
          </div>
        )}
      </header>

      {/* Hero */}
      <section id="top" className="relative overflow-hidden py-10 sm:py-16 lg:py-24">
        <div
          className="pointer-events-none absolute -top-40 -left-20 w-[520px] h-[520px] rounded-full opacity-40 blur-3xl"
          style={{ background: 'radial-gradient(circle, rgba(37,99,235,0.18), transparent 70%)' }}
        />
        <div
          className="pointer-events-none absolute top-10 -right-24 w-[460px] h-[460px] rounded-full opacity-40 blur-3xl"
          style={{ background: 'radial-gradient(circle, rgba(14,165,233,0.16), transparent 70%)' }}
        />

        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
          <div className="lg:col-span-6 min-w-0 space-y-5 sm:space-y-6">
            <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-[#eaf2fb] border border-[#dbe7f3] text-xs font-bold text-blue-700 max-w-full">
              <Sparkles className="w-3.5 h-3.5 text-sky-500 shrink-0" />
              <span className="truncate">{t(locale, 'heroBadge')}</span>
            </div>

            <h1 className="text-[1.95rem] sm:text-5xl font-extrabold tracking-tight leading-[1.18] text-[#1e3a5f] break-words">
              {t(locale, 'heroTitle1')}
              <span className="block nova-gradient-text mt-1">{t(locale, 'heroTitle2')}</span>
            </h1>

            <p className="text-sm sm:text-lg text-slate-600 max-w-xl leading-relaxed">
              {t(locale, 'heroLead')}
            </p>

            <div className="flex flex-wrap gap-3 pt-1">
              <button
                type="button"
                onClick={() => (isAuthenticated ? onNavigate('dashboard') : onNavigate('register'))}
                className="inline-flex items-center gap-2 px-5 sm:px-6 py-3 sm:py-3.5 rounded-xl text-sm font-bold text-white nova-gradient-bg shadow-lg shadow-blue-600/20 hover:opacity-95 transition cursor-pointer"
              >
                {t(locale, 'getStarted')} <ArrowRight className="w-4 h-4" />
              </button>
              <button
                type="button"
                onClick={() => onNavigate('catalog')}
                className="inline-flex items-center gap-2 px-5 sm:px-6 py-3 sm:py-3.5 rounded-xl text-sm font-bold text-[#1e3a5f] bg-white border border-[#dbe7f3] hover:bg-slate-50 transition cursor-pointer"
              >
                {t(locale, 'viewServices')}
              </button>
            </div>

            {!isAuthenticated && needsAdminSetup && (
              <div className="pt-1">
                <button
                  type="button"
                  onClick={() => onNavigate('setup-admin')}
                  className="inline-flex items-start sm:items-center gap-2 text-left text-xs font-bold px-3.5 py-2.5 rounded-xl bg-amber-50 text-amber-900 border border-amber-300 hover:bg-amber-100 transition cursor-pointer max-w-full"
                >
                  <ShieldCheck className="w-4 h-4 text-amber-600 shrink-0 mt-0.5 sm:mt-0" />
                  <span className="leading-snug">ติดตั้งครั้งแรก: สร้างบัญชีผู้ดูแลระบบคนแรก (Initial Admin Setup)</span>
                </button>
              </div>
            )}

            <div className="flex flex-wrap gap-x-5 gap-y-2.5 pt-2 text-xs sm:text-sm text-slate-600">
              <div className="flex items-center gap-1.5">
                <Check className="w-4 h-4 text-sky-500 shrink-0" />
                <span>ปลอดภัย 100% ไม่ขอรหัสโซเชียล</span>
              </div>
              <div className="flex items-center gap-1.5">
                <Check className="w-4 h-4 text-sky-500 shrink-0" />
                <span>ส่งยอดอัตโนมัติ 24/7</span>
              </div>
              <div className="flex items-center gap-1.5">
                <Check className="w-4 h-4 text-sky-500 shrink-0" />
                <span>รองรับ API v2</span>
              </div>
            </div>
          </div>

          {/* Live Database Catalog Showcase */}
          <div className="lg:col-span-6 min-w-0 relative">
            <div className="nova-card p-4 sm:p-5 nova-mock-tilt bg-gradient-to-br from-white to-[#f7fbff] overflow-hidden">
              <div className="flex items-center gap-1.5 pb-3 border-b border-[#dbe7f3] min-w-0">
                <span className="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0" />
                <span className="w-2.5 h-2.5 rounded-full bg-amber-400 shrink-0" />
                <span className="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0" />
                <span className="ml-2 text-xs text-slate-500 font-medium truncate">
                  {settings.siteName} · Live Service Catalog
                </span>
              </div>

              <div className="mt-4 p-4 sm:p-5 rounded-2xl nova-gradient-bg text-white flex items-end justify-between gap-3">
                <div className="min-w-0">
                  <div className="text-xs opacity-85">บริการที่เปิดใช้งานในระบบ</div>
                  <div className="text-2xl sm:text-3xl font-extrabold tracking-tight mt-0.5 leading-tight">
                    {publicStats.totalServices} บริการ · {platforms.length} แพลตฟอร์ม
                  </div>
                </div>
                <span className="text-xs font-semibold px-2.5 py-1 rounded-md bg-white/20 shrink-0">
                  ออนไลน์
                </span>
              </div>

              <div className="mt-4 space-y-2.5">
                {services.slice(0, 3).map((svc) => {
                  const plat = platforms.find((p) => p.id === svc.platformId);
                  return (
                    <div
                      key={svc.id}
                      className="flex items-center gap-2.5 sm:gap-3 p-2.5 sm:p-3 rounded-xl bg-white border border-[#dbe7f3] min-w-0"
                    >
                      <span
                        className="w-8 h-8 rounded-lg font-extrabold text-xs text-white grid place-items-center shrink-0"
                        style={{ backgroundColor: plat?.badgeColor || '#2563eb' }}
                      >
                        {plat?.code || 'SMM'}
                      </span>
                      <div className="flex-1 min-w-0">
                        <div className="text-xs sm:text-sm font-bold truncate">
                          <span className="font-mono text-[11px] text-blue-700 mr-1.5">{svc.productCode}</span>
                          {svc.name}
                        </div>
                        <div className="text-[11px] sm:text-xs text-slate-500 truncate">
                          ขั้นต่ำ {svc.minBuy.toLocaleString()} · {svc.speedLabel}
                        </div>
                      </div>
                      <span className="text-[11px] sm:text-xs font-bold px-2 sm:px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 shrink-0">
                        {formatCurrency(svc.pricePer1k, currency)}/1K
                      </span>
                    </div>
                  );
                })}
              </div>

              <div className="mt-4 pt-3 border-t border-[#dbe7f3] flex items-center justify-between gap-2 text-xs font-semibold text-slate-600">
                <span className="flex items-center gap-2 min-w-0">
                  <BarChart3 className="w-4 h-4 text-sky-600 shrink-0" />
                  <span className="truncate">คำสั่งซื้อ 24 ชม. ล่าสุด</span>
                </span>
                <strong className="text-sky-700 font-extrabold shrink-0">
                  {publicStats.ordersToday.toLocaleString()} รายการ
                </strong>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Platforms Strip */}
      <section className="py-7 border-y border-[#dbe7f3] bg-white/70">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 flex flex-wrap items-center justify-center gap-x-10 gap-y-3">
          <span className="text-xs font-bold uppercase tracking-wider text-slate-500">
            รองรับทุกแพลตฟอร์ม
          </span>
          {platforms.map((p) => (
            <span key={p.id} className="text-base font-bold text-slate-700">
              {p.name}
            </span>
          ))}
        </div>
      </section>

      {/* Stats */}
      <section className="py-14 max-w-7xl mx-auto px-4 sm:px-6">
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div className="nova-card p-6">
            <div className="text-3xl font-extrabold nova-gradient-text">
              {publicStats.totalServices}+
            </div>
            <div className="text-sm text-slate-500 mt-1">บริการคุณภาพพร้อมใช้งาน</div>
          </div>
          <div className="nova-card p-6">
            <div className="text-3xl font-extrabold nova-gradient-text">
              {publicStats.totalOrdersCompleted.toLocaleString()}
            </div>
            <div className="text-sm text-slate-500 mt-1">คำสั่งที่ดำเนินการสำเร็จ</div>
          </div>
          <div className="nova-card p-6">
            <div className="text-3xl font-extrabold nova-gradient-text">
              {publicStats.uptimePercent}%
            </div>
            <div className="text-sm text-slate-500 mt-1">เสถียรภาพของระบบ</div>
          </div>
          <div className="nova-card p-6">
            <div className="text-3xl font-extrabold nova-gradient-text">24/7</div>
            <div className="text-sm text-slate-500 mt-1">ระบบอัตโนมัติ & ซัพพอร์ต</div>
          </div>
        </div>
      </section>

      {/* Features */}
      <section className="py-12 max-w-7xl mx-auto px-4 sm:px-6">
        <div className="text-center max-w-2xl mx-auto mb-10">
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#eaf2fb] text-xs font-bold text-blue-700">
            <Zap className="w-3.5 h-3.5" /> ทำไมต้องเลือก {settings.siteName}
          </div>
          <h2 className="text-3xl font-extrabold mt-3">
            ทุกอย่างที่คุณต้องการเพื่อ <span className="nova-gradient-text">เติบโต</span>
          </h2>
          <p className="text-slate-600 mt-2">
            ตั้งแต่ผู้ใช้งานทั่วไปจนถึงเอเจนซี่และตัวแทนจำหน่าย
          </p>
        </div>

        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
          {[
            {
              icon: Sparkles,
              title: 'บริการหลากหลายครบวงจร',
              desc: 'ครอบคลุม TikTok, Instagram, Facebook, YouTube, Telegram, X และ Spotify',
            },
            {
              icon: ShieldCheck,
              title: 'คุณภาพเสถียร มี Refill',
              desc: 'บริการรับประกัน 30 วัน พร้อมปุ่ม Refill ชดเชยยอดอัตโนมัติในหน้าคำสั่งซื้อ',
            },
            {
              icon: Wallet,
              title: 'ราคาคุ้มค่า ส่วนลดสมาชิก',
              desc: 'ระบบระดับสมาชิก Gold, Platinum, Agency รับส่วนลดสูงสุด 12% ทุกออเดอร์',
            },
            {
              icon: Code2,
              title: 'SMM API v2 มาตรฐานสากล',
              desc: 'เชื่อมต่อเว็บลูกข่ายและสคริปต์อัตโนมัติได้ทันทีผ่าน Endpoint /api/v2',
            },
            {
              icon: BarChart3,
              title: 'ติดตามผลแบบเรียลไทม์',
              desc: 'ดูความคืบหน้า ยอดเริ่มต้น คงเหลือ และประวัติธุรกรรมย้อนหลังครบถ้วน',
            },
            {
              icon: Headphones,
              title: 'พร้อมให้บริการ 24 ชั่วโมง',
              desc: 'ระบบเติมเงิน PromptPay QR, โอนผ่านธนาคาร, Voucher และซัพพอร์ตต่อเนื่อง',
            },
          ].map((f) => (
            <div key={f.title} className="nova-card p-6 hover:-translate-y-1 transition-transform">
              <div className="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 grid place-items-center mb-4">
                <f.icon className="w-5 h-5" />
              </div>
              <h3 className="font-bold text-base">{f.title}</h3>
              <p className="text-sm text-slate-600 mt-1.5 leading-relaxed">{f.desc}</p>
            </div>
          ))}
        </div>
      </section>

      {/* Popular Services Preview */}
      <section className="py-14 bg-white/75 border-y border-[#dbe7f3]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6">
          <div className="flex flex-wrap items-end justify-between gap-4 mb-8">
            <div>
              <h2 className="text-2xl sm:text-3xl font-extrabold">
                บริการยอดนิยม <span className="nova-gradient-text">ราคาเริ่มต้นสุดคุ้ม</span>
              </h2>
              <p className="text-sm text-slate-600 mt-1">
                อัปเดตราคาต่อ 1,000 หน่วยแบบโปร่งใส
              </p>
            </div>
            <button
              type="button"
              onClick={() => onNavigate('catalog')}
              className="inline-flex items-center gap-1.5 text-sm font-bold text-blue-600 hover:underline cursor-pointer"
            >
              ดูบริการทั้งหมด ({services.length}) <ArrowRight className="w-4 h-4" />
            </button>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {services.slice(0, 6).map((svc) => {
              const plat = platforms.find((p) => p.id === svc.platformId);
              return (
                <div key={svc.id} className="nova-card p-5 flex flex-col justify-between gap-3">
                  <div>
                    <div className="flex items-center justify-between gap-2 mb-2">
                      <div className="flex items-center gap-1.5 min-w-0">
                        <span
                          className="text-[11px] font-bold px-2 py-0.5 rounded text-white truncate"
                          style={{ backgroundColor: plat?.badgeColor || '#2563eb' }}
                        >
                          {plat?.name || 'STORE'}
                        </span>
                        <span className="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">
                          {svc.productCode}
                        </span>
                      </div>
                      <span className="text-sm font-extrabold text-blue-600 shrink-0">
                        {formatCurrency(svc.pricePer1k, currency)} / 1K
                      </span>
                    </div>
                    <h3 className="font-bold text-sm leading-snug">{svc.name}</h3>
                    <p className="text-xs text-slate-500 mt-1.5 line-clamp-2">{svc.description}</p>
                  </div>
                  <div className="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
                    <span>
                      ขั้นต่ำ {svc.minBuy.toLocaleString()} · สูงสุด {svc.maxBuy.toLocaleString()}
                    </span>
                    <button
                      type="button"
                      onClick={() =>
                        isAuthenticated ? onNavigate('dashboard') : onNavigate('register')
                      }
                      className="font-bold text-blue-600 hover:underline cursor-pointer"
                    >
                      สั่งซื้อ
                    </button>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      {/* How it works */}
      <section id="how" className="py-16 max-w-7xl mx-auto px-4 sm:px-6">
        <div className="text-center max-w-xl mx-auto mb-10">
          <h2 className="text-3xl font-extrabold">
            เพียง 3 ขั้นตอน <span className="nova-gradient-text">เริ่มใช้งานได้เลย</span>
          </h2>
        </div>
        <div className="grid md:grid-cols-3 gap-5">
          {[
            {
              step: '01',
              title: 'สมัครสมาชิกและเติมเงิน',
              desc: 'สร้างบัญชีฟรีใน 10 วินาที เติมเงินผ่าน PromptPay, โอนธนาคาร หรือแลกโค้ดโปรโมชั่น (Voucher)',
            },
            {
              step: '02',
              title: 'เลือกบริการและใส่ลิงก์',
              desc: 'เลือกแพลตฟอร์มและบริการที่ต้องการ ใส่ลิงก์เป้าหมายและจำนวน ระบบคำนวณราคาทันที',
            },
            {
              step: '03',
              title: 'ติดตามผลลัพธ์เรียลไทม์',
              desc: 'คำสั่งซื้อเริ่มทำงานอัตโนมัติ พร้อมตรวจสอบสถานะ Refill หรือคืนเงินได้ในหน้าเดียว',
            },
          ].map((item) => (
            <div key={item.step} className="nova-card p-6 relative overflow-hidden">
              <span className="absolute top-3 right-5 text-4xl font-black text-blue-600/10">
                {item.step}
              </span>
              <h3 className="font-bold text-lg">{item.title}</h3>
              <p className="text-sm text-slate-600 mt-2 leading-relaxed">{item.desc}</p>
            </div>
          ))}
        </div>
      </section>

      {/* FAQ */}
      <section id="faq" className="py-16 bg-white/75 border-t border-[#dbe7f3]">
        <div className="max-w-4xl mx-auto px-4 sm:px-6">
          <h2 className="text-2xl sm:text-3xl font-extrabold text-center mb-8">
            คำถามที่พบบ่อย (FAQ)
          </h2>
          <div className="space-y-3">
            {faqs.map((item) => (
              <details
                key={item.q}
                className="nova-card px-5 py-4 group"
              >
                <summary className="font-bold text-sm sm:text-base cursor-pointer list-none flex items-center justify-between gap-4">
                  <span>{item.q}</span>
                  <span className="text-blue-600 text-lg font-bold">+</span>
                </summary>
                <p className="text-sm text-slate-600 mt-3 pt-3 border-t border-slate-100 leading-relaxed">
                  {item.a}
                </p>
              </details>
            ))}
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="py-8 border-t border-[#dbe7f3] bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
          <div className="flex items-center gap-2 font-semibold text-[#1e3a5f]">
            <img src="/assets/images/brand-logos/nova-icon.svg" alt="" className="w-5 h-5" />
            <span>{settings.siteName} © {new Date().getFullYear()} — All rights reserved.</span>
          </div>
          <div className="flex items-center gap-5">
            <button type="button" onClick={() => onNavigate('catalog')} className="hover:text-blue-600 cursor-pointer">
              บริการทั้งหมด
            </button>
            <button type="button" onClick={() => onNavigate('api-docs')} className="hover:text-blue-600 cursor-pointer">
              API v2
            </button>
            <button type="button" onClick={() => onNavigate('terms')} className="hover:text-blue-600 cursor-pointer">
              ข้อกำหนดการใช้งาน
            </button>
          </div>
        </div>
      </footer>
    </div>
  );
}
