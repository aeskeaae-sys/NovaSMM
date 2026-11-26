import { useEffect, useState, type ReactNode } from 'react';
import {
  BarChart3,
  Bell,
  BookOpen,
  Code2,
  CreditCard,
  History,
  ListOrdered,
  LogOut,
  Menu,
  Moon,
  Search,
  Server,
  Shield,
  ShoppingCart,
  Sun,
  User as UserIcon,
  Users,
  X,
} from 'lucide-react';
import type {
  Category,
  CurrencyCode,
  LocaleCode,
  NotificationItem,
  Order,
  Post,
  Service,
  User,
} from '../types/smm';
import { formatCurrency } from '../utils/pricing';
import { t } from '../i18n/translations';
import { apiRequest } from '../utils/api';

export type AppView =
  | 'dashboard'
  | 'orders'
  | 'catalog'
  | 'deposits'
  | 'transactions'
  | 'affiliates'
  | 'statistics'
  | 'api-docs'
  | 'articles'
  | 'profile'
  | 'admin'
  | 'terms';

interface AppShellProps {
  user: User;
  siteName: string;
  siteTagline?: string;
  activeView: AppView;
  locale: LocaleCode;
  currency: CurrencyCode;
  theme: 'light' | 'dark';
  announcement: string;
  onNavigate: (view: AppView) => void;
  onChangeLocale: (l: LocaleCode) => void;
  onChangeCurrency: (c: CurrencyCode) => void;
  onToggleTheme: () => void;
  onLogout: () => void;
  children: ReactNode;
}

export function AppShell({
  user,
  siteName,
  siteTagline,
  activeView,
  locale,
  currency,
  theme,
  announcement,
  onNavigate,
  onChangeLocale,
  onChangeCurrency,
  onToggleTheme,
  onLogout,
  children,
}: AppShellProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [notifOpen, setNotifOpen] = useState(false);
  const [notifications, setNotifications] = useState<NotificationItem[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);

  // Global search overlay (Ctrl+K)
  const [searchOpen, setSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [searchResults, setSearchResults] = useState<{
    services: Service[];
    categories: Category[];
    orders: Order[];
    posts: Post[];
    members: Array<{ id: number; username: string; email: string; rank: string }>;
  }>({ services: [], categories: [], orders: [], posts: [], members: [] });

  async function loadNotifications() {
    try {
      const res = await apiRequest<{ notifications: NotificationItem[]; unreadCount: number }>(
        '/api/notifications'
      );
      setNotifications(res.notifications);
      setUnreadCount(res.unreadCount);
    } catch {
      // Ignore transient errors
    }
  }

  useEffect(() => {
    loadNotifications();
    const timer = setInterval(loadNotifications, 15000);
    return () => clearInterval(timer);
  }, [user.id, activeView]);

  useEffect(() => {
    function handleKey(e: KeyboardEvent) {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        setSearchOpen(true);
      } else if (e.key === 'Escape') {
        setSearchOpen(false);
        setNotifOpen(false);
      }
    }
    window.addEventListener('keydown', handleKey);
    return () => window.removeEventListener('keydown', handleKey);
  }, []);

  useEffect(() => {
    if (!searchOpen) return;
    const q = searchQuery.trim();
    if (!q) {
      setSearchResults({ services: [], categories: [], orders: [], posts: [], members: [] });
      return;
    }
    const timer = setTimeout(async () => {
      try {
        const res = await apiRequest<{
          services: Service[];
          categories: Category[];
          orders: Order[];
          posts: Post[];
          members: Array<{ id: number; username: string; email: string; rank: string }>;
        }>(`/api/search?q=${encodeURIComponent(q)}`);
        setSearchResults(res);
      } catch {
        // Ignore
      }
    }, 150);
    return () => clearTimeout(timer);
  }, [searchQuery, searchOpen]);

  async function markAllRead() {
    await apiRequest('/api/notifications/read', { method: 'POST', body: JSON.stringify({}) });
    await loadNotifications();
  }

  const navGroups: Array<{
    label: string;
    items: Array<{ id: AppView; label: string; icon: typeof ShoppingCart }>;
  }> = [
    {
      label: 'สินค้าและบริการ',
      items: [
        { id: 'dashboard', label: t(locale, 'newOrder'), icon: ShoppingCart },
        { id: 'catalog', label: t(locale, 'navServices'), icon: Server },
        { id: 'statistics', label: t(locale, 'statistics'), icon: BarChart3 },
      ],
    },
    {
      label: 'บัญชีและการเงิน',
      items: [
        { id: 'orders', label: t(locale, 'orders'), icon: ListOrdered },
        { id: 'deposits', label: t(locale, 'deposits'), icon: CreditCard },
        { id: 'transactions', label: t(locale, 'transactions'), icon: History },
        { id: 'affiliates', label: t(locale, 'affiliates'), icon: Users },
      ],
    },
    {
      label: 'เครื่องมือและคู่มือ',
      items: [
        { id: 'api-docs', label: t(locale, 'navApi'), icon: Code2 },
        { id: 'articles', label: t(locale, 'articles'), icon: BookOpen },
        { id: 'profile', label: t(locale, 'profile'), icon: UserIcon },
      ],
    },
  ];

  const SidebarContent = (
    <div className="flex flex-col h-full bg-white border-r border-[#dbe7f3]">
      <div className="h-16 px-5 flex items-center justify-between border-b border-[#dbe7f3]">
        <button
          type="button"
          onClick={() => {
            onNavigate('dashboard');
            setSidebarOpen(false);
          }}
          className="flex items-center gap-2.5 text-left cursor-pointer"
        >
          <img src="/assets/images/brand-logos/nova-icon.svg" alt={siteName} className="w-9 h-9" />
          <div className="leading-tight min-w-0">
            <div className="text-lg font-extrabold nova-gradient-text tracking-tight truncate">{siteName}</div>
            <div className="text-[11px] font-medium text-slate-500 truncate">
              {siteTagline || t(locale, 'brandTagline')}
            </div>
          </div>
        </button>
        <button
          type="button"
          aria-label="Close sidebar"
          onClick={() => setSidebarOpen(false)}
          className="lg:hidden p-1.5 rounded-lg text-slate-500 hover:bg-slate-100"
        >
          <X className="w-5 h-5" />
        </button>
      </div>

      <nav aria-label="Main Navigation" className="flex-1 overflow-y-auto py-4 pr-3 space-y-5">
        {navGroups.map((group) => (
          <div key={group.label}>
            <div className="px-5 py-2 mb-1.5 bg-[#eaf2fb] rounded-r-xl text-[11px] font-bold uppercase tracking-wider text-blue-800 dark:text-sky-300">
              {group.label}
            </div>
            <ul className="space-y-1 pl-3">
              {group.items.map((item) => {
                const active = activeView === item.id;
                return (
                  <li key={item.id}>
                    <button
                      type="button"
                      onClick={() => {
                        onNavigate(item.id);
                        setSidebarOpen(false);
                      }}
                      className={`w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition cursor-pointer ${
                        active
                          ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20'
                          : 'text-slate-600 hover:bg-[#f4f8fd] hover:text-blue-700'
                      }`}
                    >
                      <item.icon className="w-4 h-4 shrink-0" />
                      <span className="truncate">{item.label}</span>
                    </button>
                  </li>
                );
              })}
            </ul>
          </div>
        ))}

        {user.role === 'admin' && (
          <div>
            <div className="px-5 py-2 mb-1.5 bg-amber-50 dark:bg-amber-950/40 rounded-r-xl text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">
              ผู้ดูแลระบบ
            </div>
            <ul className="space-y-1 pl-3">
              <li>
                <button
                  type="button"
                  onClick={() => {
                    onNavigate('admin');
                    setSidebarOpen(false);
                  }}
                  className={`w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition cursor-pointer ${
                    activeView === 'admin'
                      ? 'bg-slate-900 text-white shadow-sm'
                      : 'text-slate-700 hover:bg-amber-50 hover:text-amber-800'
                  }`}
                >
                  <Shield className="w-4 h-4 shrink-0" />
                  <span>{t(locale, 'adminPanel')}</span>
                </button>
              </li>
            </ul>
          </div>
        )}
      </nav>

      <div className="p-4 border-t border-[#dbe7f3] bg-[#f8fbff] space-y-2.5">
        <div className="flex items-center justify-between text-xs">
          <span className="text-slate-500 font-medium">ระดับสมาชิก</span>
          <span className="px-2 py-0.5 rounded bg-blue-100 text-blue-700 font-bold uppercase">
            {user.rank}
          </span>
        </div>
        <div className="sm:hidden flex items-center justify-between text-xs">
          <span className="text-slate-500 font-medium">ภาษา / Language</span>
          <select
            aria-label="Mobile Language"
            value={locale}
            onChange={(e) => onChangeLocale(e.target.value as LocaleCode)}
            className="text-xs font-bold bg-white border border-[#dbe7f3] rounded-lg px-2 py-1 text-[#1e3a5f]"
          >
            <option value="th">TH ไทย</option>
            <option value="en">EN</option>
            <option value="vi">VI</option>
          </select>
        </div>
        <button
          type="button"
          onClick={onLogout}
          className="w-full flex items-center justify-center gap-2 py-2 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 transition cursor-pointer"
        >
          <LogOut className="w-3.5 h-3.5" /> {t(locale, 'logout')}
        </button>
      </div>
    </div>
  );

  return (
    <div className="min-h-screen bg-[#f4f8fd] flex">
      {/* Desktop Sidebar */}
      <aside className="hidden lg:block w-64 shrink-0 sticky top-0 h-screen">
        {SidebarContent}
      </aside>

      {/* Mobile Sidebar Drawer */}
      {sidebarOpen && (
        <div className="fixed inset-0 z-50 lg:hidden flex">
          <div
            className="fixed inset-0 bg-slate-900/40 backdrop-blur-xs"
            onClick={() => setSidebarOpen(false)}
          />
          <aside className="relative w-72 max-w-[85vw] h-full z-10">{SidebarContent}</aside>
        </div>
      )}

      {/* Main Column */}
      <div className="flex-1 min-w-0 flex flex-col">
        {/* Topbar */}
        <header className="sticky top-0 z-30 h-16 bg-white/95 backdrop-blur-md border-b border-[#dbe7f3] px-4 sm:px-6 flex items-center justify-between gap-3">
          <div className="flex items-center gap-3">
            <button
              type="button"
              aria-label="Open navigation menu"
              onClick={() => setSidebarOpen(true)}
              className="lg:hidden p-2 rounded-xl border border-[#dbe7f3] text-[#1e3a5f]"
            >
              <Menu className="w-5 h-5" />
            </button>

            <button
              type="button"
              onClick={() => setSearchOpen(true)}
              className="hidden sm:flex items-center gap-2.5 px-3.5 py-2 rounded-xl bg-[#f4f8fd] border border-[#dbe7f3] text-xs text-slate-500 hover:border-blue-300 transition w-64 md:w-80 cursor-pointer"
            >
              <Search className="w-4 h-4 text-slate-400" />
              <span className="truncate">{t(locale, 'searchPlaceholder')}</span>
            </button>
          </div>

          <div className="flex items-center gap-2 sm:gap-2.5">
            <button
              type="button"
              onClick={() => setSearchOpen(true)}
              aria-label="Search"
              className="sm:hidden p-2 rounded-xl border border-[#dbe7f3] text-slate-600"
            >
              <Search className="w-4 h-4" />
            </button>

            {/* Theme Toggle */}
            <button
              type="button"
              aria-label="Toggle color theme"
              onClick={onToggleTheme}
              className="p-2 rounded-xl border border-[#dbe7f3] bg-white hover:bg-slate-50 text-slate-600 cursor-pointer"
              title={theme === 'dark' ? 'โหมดสว่าง' : 'โหมดมืด'}
            >
              {theme === 'dark' ? <Sun className="w-4 h-4 text-amber-400" /> : <Moon className="w-4 h-4" />}
            </button>

            {/* Currency selector */}
            <label htmlFor="topbar-currency" className="sr-only">
              Currency
            </label>
            <select
              id="topbar-currency"
              value={currency}
              onChange={(e) => onChangeCurrency(e.target.value as CurrencyCode)}
              className="text-xs font-bold bg-[#f4f8fd] border border-[#dbe7f3] rounded-xl px-2.5 py-2 text-[#1e3a5f]"
            >
              <option value="THB">฿ THB</option>
              <option value="USD">$ USD</option>
              <option value="VND">₫ VND</option>
            </select>

            {/* Language selector */}
            <label htmlFor="topbar-locale" className="sr-only">
              Language
            </label>
            <select
              id="topbar-locale"
              value={locale}
              onChange={(e) => onChangeLocale(e.target.value as LocaleCode)}
              className="hidden sm:block text-xs font-bold bg-[#f4f8fd] border border-[#dbe7f3] rounded-xl px-2.5 py-2 text-[#1e3a5f]"
            >
              <option value="th">TH</option>
              <option value="en">EN</option>
              <option value="vi">VI</option>
            </select>

            {/* Notifications Bell */}
            <div className="relative">
              <button
                type="button"
                aria-label="Notifications"
                onClick={() => setNotifOpen((v) => !v)}
                className="relative p-2 rounded-xl border border-[#dbe7f3] bg-white hover:bg-slate-50 text-slate-600 cursor-pointer"
              >
                <Bell className="w-4 h-4" />
                {unreadCount > 0 && (
                  <span className="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold grid place-items-center">
                    {unreadCount}
                  </span>
                )}
              </button>

              {notifOpen && (
                <div className="absolute right-0 mt-2 w-80 sm:w-96 nova-card p-3 z-50 shadow-xl">
                  <div className="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                    <span className="text-xs font-bold">{t(locale, 'notifications')}</span>
                    {unreadCount > 0 && (
                      <button
                        type="button"
                        onClick={markAllRead}
                        className="text-[11px] font-semibold text-blue-600 hover:underline cursor-pointer"
                      >
                        {t(locale, 'markAllRead')}
                      </button>
                    )}
                  </div>
                  <div className="max-h-72 overflow-y-auto space-y-2">
                    {notifications.length === 0 ? (
                      <div className="py-6 text-center text-xs text-slate-500">
                        {t(locale, 'noNotifications')}
                      </div>
                    ) : (
                      notifications.map((n) => (
                        <div
                          key={n.id}
                          className={`p-2.5 rounded-xl border text-xs ${
                            n.read
                              ? 'bg-white border-slate-100 text-slate-600'
                              : 'bg-blue-50/60 border-blue-100 text-[#1e3a5f]'
                          }`}
                        >
                          <div className="font-bold">{n.title}</div>
                          <div className="text-slate-500 mt-0.5">{n.message}</div>
                        </div>
                      ))
                    )}
                  </div>
                </div>
              )}
            </div>

            {/* Balance Pill */}
            <button
              type="button"
              onClick={() => onNavigate('deposits')}
              className="flex items-center gap-2 px-3 py-1.5 rounded-xl nova-gradient-bg text-white text-xs font-bold shadow-sm hover:opacity-95 transition cursor-pointer"
            >
              <span className="opacity-85 hidden sm:inline">{t(locale, 'balance')}:</span>
              <span data-testid="user-balance">{formatCurrency(user.balance, currency)}</span>
            </button>

            <button
              type="button"
              onClick={() => onNavigate('profile')}
              className="hidden md:flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-xl border border-[#dbe7f3] bg-white hover:bg-slate-50 cursor-pointer"
            >
              <span className="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 font-bold text-xs grid place-items-center">
                {user.username.slice(0, 2).toUpperCase()}
              </span>
              <span className="text-xs font-bold truncate max-w-[100px]">{user.username}</span>
            </button>
          </div>
        </header>

        {/* Announcement Banner */}
        {announcement && (
          <div className="bg-[#eaf2fb] border-b border-[#dbe7f3] px-4 sm:px-6 py-2 text-xs text-blue-900 dark:text-sky-200 font-medium flex items-center justify-between gap-2">
            <span>{announcement}</span>
          </div>
        )}

        {/* Main Content */}
        <main className="flex-1 p-4 sm:p-6 max-w-7xl w-full mx-auto">{children}</main>
      </div>

      {/* Global Search Modal (Ctrl+K) */}
      {searchOpen && (
        <div
          role="dialog"
          aria-label="Global Search"
          className="fixed inset-0 z-50 flex items-start justify-center pt-20 px-4"
        >
          <div
            className="fixed inset-0 bg-slate-900/45 backdrop-blur-xs"
            onClick={() => setSearchOpen(false)}
          />
          <div className="relative w-full max-w-xl nova-card p-4 shadow-2xl z-10">
            <div className="flex items-center gap-2.5 pb-3 border-b border-slate-100">
              <Search className="w-4 h-4 text-slate-400" />
              <input
                autoFocus
                type="search"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="ค้นหาชื่อบริการ, หมวดหมู่, คำสั่งซื้อ, บทความ..."
                className="flex-1 text-sm bg-transparent focus:outline-none"
              />
              <button
                type="button"
                onClick={() => setSearchOpen(false)}
                className="text-xs font-bold px-2 py-1 rounded bg-slate-100 text-slate-600 cursor-pointer"
              >
                ESC
              </button>
            </div>

            <div className="mt-3 max-h-80 overflow-y-auto space-y-4">
              {!searchQuery.trim() ? (
                <div className="flex flex-wrap gap-2 py-2">
                  {[
                    { label: 'สั่งซื้อบริการใหม่', view: 'dashboard' as AppView },
                    { label: 'ประวัติคำสั่งซื้อ', view: 'orders' as AppView },
                    { label: 'เติมเงิน / แลกโค้ด', view: 'deposits' as AppView },
                    { label: 'เอกสาร API v2', view: 'api-docs' as AppView },
                  ].map((s) => (
                    <button
                      key={s.view}
                      type="button"
                      onClick={() => {
                        onNavigate(s.view);
                        setSearchOpen(false);
                      }}
                      className="text-xs font-semibold px-3 py-1.5 rounded-lg bg-[#eaf2fb] text-blue-700 hover:bg-blue-100 cursor-pointer"
                    >
                      {s.label}
                    </button>
                  ))}
                </div>
              ) : (
                <>
                  {searchResults.services.length > 0 && (
                    <div>
                      <div className="text-[11px] font-bold uppercase text-slate-400 mb-1.5">
                        บริการ ({searchResults.services.length})
                      </div>
                      <div className="space-y-1">
                        {searchResults.services.map((s) => (
                          <button
                            key={s.id}
                            type="button"
                            onClick={() => {
                              onNavigate('dashboard');
                              setSearchOpen(false);
                            }}
                            className="w-full text-left px-3 py-2 rounded-xl hover:bg-[#f4f8fd] flex items-center justify-between gap-2 text-xs cursor-pointer"
                          >
                            <span className="font-semibold truncate">
                              <span className="font-mono text-blue-700 mr-1.5">[{s.productCode}]</span>
                              {s.name}
                            </span>
                            <span className="font-bold text-blue-600 shrink-0">
                              {formatCurrency(s.pricePer1k, currency)}/1K
                            </span>
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  {searchResults.categories.length > 0 && (
                    <div>
                      <div className="text-[11px] font-bold uppercase text-slate-400 mb-1.5">
                        หมวดหมู่ ({searchResults.categories.length})
                      </div>
                      <div className="space-y-1">
                        {searchResults.categories.map((c) => (
                          <button
                            key={c.id}
                            type="button"
                            onClick={() => {
                              onNavigate('catalog');
                              setSearchOpen(false);
                            }}
                            className="w-full text-left px-3 py-2 rounded-xl hover:bg-[#f4f8fd] text-xs font-semibold cursor-pointer"
                          >
                            {c.name}
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  {searchResults.orders.length > 0 && (
                    <div>
                      <div className="text-[11px] font-bold uppercase text-slate-400 mb-1.5">
                        คำสั่งซื้อ ({searchResults.orders.length})
                      </div>
                      <div className="space-y-1">
                        {searchResults.orders.map((o) => (
                          <button
                            key={o.id}
                            type="button"
                            onClick={() => {
                              onNavigate('orders');
                              setSearchOpen(false);
                            }}
                            className="w-full text-left px-3 py-2 rounded-xl hover:bg-[#f4f8fd] flex items-center justify-between gap-2 text-xs cursor-pointer"
                          >
                            <span className="font-semibold truncate">
                              #{o.id} ({o.orderCode}) · {o.serviceName}
                            </span>
                            <span className="text-slate-500 shrink-0">{o.status}</span>
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  {searchResults.posts.length > 0 && (
                    <div>
                      <div className="text-[11px] font-bold uppercase text-slate-400 mb-1.5">
                        บทความและคู่มือ ({searchResults.posts.length})
                      </div>
                      <div className="space-y-1">
                        {searchResults.posts.map((p) => (
                          <button
                            key={p.id}
                            type="button"
                            onClick={() => {
                              onNavigate('articles');
                              setSearchOpen(false);
                            }}
                            className="w-full text-left px-3 py-2 rounded-xl hover:bg-[#f4f8fd] text-xs font-semibold cursor-pointer"
                          >
                            {p.title}
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  {searchResults.members.length > 0 && (
                    <div>
                      <div className="text-[11px] font-bold uppercase text-slate-400 mb-1.5">
                        สมาชิก (Admin)
                      </div>
                      <div className="space-y-1">
                        {searchResults.members.map((m) => (
                          <button
                            key={m.id}
                            type="button"
                            onClick={() => {
                              onNavigate('admin');
                              setSearchOpen(false);
                            }}
                            className="w-full text-left px-3 py-2 rounded-xl hover:bg-[#f4f8fd] flex justify-between text-xs cursor-pointer"
                          >
                            <span className="font-semibold">{m.username}</span>
                            <span className="text-slate-500">{m.email}</span>
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  {searchResults.services.length === 0 &&
                    searchResults.categories.length === 0 &&
                    searchResults.orders.length === 0 &&
                    searchResults.posts.length === 0 &&
                    searchResults.members.length === 0 && (
                      <div className="py-6 text-center text-xs text-slate-500">
                        ไม่พบผลลัพธ์ที่ตรงกับ "{searchQuery}"
                      </div>
                    )}
                </>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
