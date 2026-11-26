import { useCallback, useEffect, useState } from 'react';
import type {
  Category,
  CurrencyCode,
  LocaleCode,
  Platform,
  Post,
  RankTierConfig,
  Service,
  SiteSettings,
  User,
} from './types/smm';
import { apiRequest } from './utils/api';
import { LandingPage } from './pages/LandingPage';
import { AuthPage } from './pages/AuthPage';
import { AppShell, type AppView } from './components/AppShell';
import { NewOrderPage } from './pages/NewOrderPage';
import { OrdersPage } from './pages/OrdersPage';
import { DepositsPage } from './pages/DepositsPage';
import { ServicesCatalogPage } from './pages/ServicesCatalogPage';
import { ApiDocsPage } from './pages/ApiDocsPage';
import {
  AffiliatesPage,
  ArticlesPage,
  ProfilePage,
  StatisticsPage,
  TermsPage,
  TransactionsPage,
} from './pages/AccountExtrasPages';
import { AdminPage } from './pages/AdminPage';

type Screen =
  | 'landing'
  | 'login'
  | 'register'
  | 'setup-admin'
  | 'public-catalog'
  | 'public-api'
  | 'public-terms'
  | AppView;

interface BootstrapResponse {
  user: User | null;
  needsAdminSetup: boolean;
  platforms: Platform[];
  categories: Category[];
  services: Service[];
  posts: Post[];
  rankTiers: RankTierConfig[];
  settings: SiteSettings;
  publicStats: {
    totalServices: number;
    totalOrdersCompleted: number;
    ordersToday: number;
    totalUsers: number;
    uptimePercent: number;
  };
}

export default function App() {
  const [loading, setLoading] = useState(true);
  const [bootstrap, setBootstrap] = useState<BootstrapResponse | null>(null);
  const [user, setUser] = useState<User | null>(null);
  const [screen, setScreen] = useState<Screen>('landing');
  const [locale, setLocale] = useState<LocaleCode>('th');
  const [currency, setCurrency] = useState<CurrencyCode>('THB');
  const [theme, setTheme] = useState<'light' | 'dark'>('light');
  const [preselectedServiceId, setPreselectedServiceId] = useState<number | null>(null);
  const [refCode, setRefCode] = useState<string>('');
  const [latestRawApiKey, setLatestRawApiKey] = useState<string | null>(null);

  useEffect(() => {
    const root = document.documentElement;
    if (theme === 'dark') root.classList.add('dark');
    else root.classList.remove('dark');
  }, [theme]);

  useEffect(() => {
    if (bootstrap?.settings?.siteName) {
      document.title = `${bootstrap.settings.siteName} — ${
        bootstrap.settings.siteTagline || bootstrap.settings.siteDescription
      }`;
    }
  }, [bootstrap?.settings]);

  const loadBootstrap = useCallback(async () => {
    const data = await apiRequest<BootstrapResponse>('/api/bootstrap');
    setBootstrap(data);
    setUser(data.user);
    if (data.user) {
      setLocale(data.user.language || 'th');
      setCurrency(data.user.currency || 'THB');
    }
    return data;
  }, []);

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const ref = params.get('ref');
    if (ref) setRefCode(ref);
    const m = window.location.pathname.match(/^\/join\/([^/]+)/i);
    if (m?.[1]) setRefCode(decodeURIComponent(m[1]));

    loadBootstrap()
      .then((data) => {
        const fromPath = routeFromPath(window.location.pathname);
        if (data.user) {
          if (fromPath && ['landing','login','register','public-catalog','public-api','public-terms'].includes(fromPath)) {
            if (fromPath === 'public-catalog') setScreen('catalog');
            else if (fromPath === 'public-api') setScreen('api-docs');
            else setScreen(fromPath === 'landing' ? 'dashboard' : (fromPath as Screen));
          } else if (fromPath) {
            setScreen(fromPath);
          } else {
            setScreen('dashboard');
          }
        } else if (fromPath) {
          setScreen(fromPath);
        } else if (ref || m?.[1]) {
          setScreen('register');
        }
      })
      .finally(() => setLoading(false));
  }, [loadBootstrap]);

  async function handleLogout() {
    await apiRequest('/api/auth/logout', { method: 'POST' });
    setUser(null);
    setLatestRawApiKey(null);
    setScreen('landing');
    await loadBootstrap();
  }

  async function handleChangeLocale(next: LocaleCode) {
    setLocale(next);
    if (user) {
      const res = await apiRequest<{ user: User }>('/api/profile/update', {
        method: 'POST',
        body: JSON.stringify({ language: next }),
      }).catch(() => null);
      if (res?.user) setUser(res.user);
    }
  }

  async function handleChangeCurrency(next: CurrencyCode) {
    setCurrency(next);
    if (user) {
      const res = await apiRequest<{ user: User }>('/api/profile/update', {
        method: 'POST',
        body: JSON.stringify({ currency: next }),
      }).catch(() => null);
      if (res?.user) setUser(res.user);
    }
  }

  function routeFromPath(pathname: string): Screen | null {
    const p = pathname.replace(/\/+$/, '') || '/';
    if (p === '/') return 'landing';
    if (p === '/home') return 'dashboard';
    if (p === '/login') return 'login';
    if (p === '/register' || p.startsWith('/join/')) return 'register';
    if (p === '/pages/services' || p === '/services') return user ? 'catalog' : 'public-catalog';
    if (p === '/pages/api-docs') return user ? 'api-docs' : 'public-api';
    if (p === '/pages/privacy-policy' || p === '/terms-of-service') return 'terms';
    if (p === '/account/orders' || p === '/orders') return 'orders';
    if (p === '/account/profile') return 'profile';
    if (p === '/account/transactions') return 'transactions';
    if (p === '/pages/affiliates') return 'affiliates';
    if (p === '/pages/statistics') return 'statistics';
    if (p.startsWith('/account/deposits/')) return 'deposits';
    if (p.startsWith('/articles')) return 'articles';
    if (p.startsWith('/admin')) return 'admin';
    return null;
  }

  function navigatePath(pathname: string) {
    window.history.pushState({}, '', pathname);
    const next = routeFromPath(pathname);
    if (next) setScreen(next);
  }

  function handleNavigateFromLanding(target: string) {
    if (target === 'catalog') navigatePath('/pages/services');
    else if (target === 'api-docs') navigatePath('/pages/api-docs');
    else if (target === 'terms') navigatePath('/terms-of-service');
    else if (target === 'login') navigatePath('/login');
    else if (target === 'register') navigatePath('/register');
    else if (target === 'setup-admin') setScreen('setup-admin');
    else if (target === 'dashboard') navigatePath('/home');
  }

  function handleOrderService(serviceId: number) {
    setPreselectedServiceId(serviceId);
    navigatePath(user ? '/home' : '/login');
  }

  if (loading || !bootstrap) {
    return (
      <div className="min-h-screen bg-[#f4f8fd] flex items-center justify-center">
        <div className="flex flex-col items-center gap-3">
          <img
            src="/assets/images/brand-logos/nova-icon.svg"
            alt="NovaSMM"
            className="w-12 h-12 animate-pulse"
          />
          <span className="text-sm font-bold text-[#1e3a5f]">กำลังโหลดหน้าร้าน...</span>
        </div>
      </div>
    );
  }

  if (!user) {
    if (screen === 'login' || screen === 'register' || screen === 'setup-admin') {
      return (
        <AuthPage
          mode={screen}
          locale={locale}
          siteName={bootstrap.settings.siteName}
          siteTagline={bootstrap.settings.siteTagline}
          needsAdminSetup={bootstrap.needsAdminSetup}
          initialRefCode={refCode}
          onSuccess={async (u) => {
            setUser(u);
            setScreen(u.role === 'admin' && bootstrap.needsAdminSetup ? 'admin' : 'dashboard');
            await loadBootstrap();
          }}
          onSwitchMode={(m) => setScreen(m)}
          onBackHome={() => setScreen('landing')}
        />
      );
    }

    if (screen === 'public-catalog') {
      return (
        <ServicesCatalogPage
          isPublic
          siteName={bootstrap.settings.siteName}
          currency={currency}
          platforms={bootstrap.platforms}
          categories={bootstrap.categories}
          services={bootstrap.services}
          onBackHome={() => setScreen('landing')}
          onOrderService={handleOrderService}
        />
      );
    }

    if (screen === 'public-api') {
      return (
        <ApiDocsPage
          isPublic
          siteName={bootstrap.settings.siteName}
          user={null}
          onBackHome={() => setScreen('landing')}
        />
      );
    }

    if (screen === 'public-terms') {
      return <TermsPage siteName={bootstrap.settings.siteName} onBack={() => setScreen('landing')} />;
    }

    return (
      <LandingPage
        locale={locale}
        currency={currency}
        theme={theme}
        needsAdminSetup={bootstrap.needsAdminSetup}
        settings={bootstrap.settings}
        platforms={bootstrap.platforms}
        services={bootstrap.services}
        publicStats={bootstrap.publicStats}
        isAuthenticated={false}
        onChangeLocale={handleChangeLocale}
        onToggleTheme={() => setTheme((prev) => (prev === 'light' ? 'dark' : 'light'))}
        onNavigate={handleNavigateFromLanding}
      />
    );
  }

  const activeView: AppView =
    screen === 'landing' ||
    screen === 'login' ||
    screen === 'register' ||
    screen === 'setup-admin' ||
    screen === 'public-catalog' ||
    screen === 'public-api' ||
    screen === 'public-terms'
      ? 'dashboard'
      : screen;

  return (
    <AppShell
      user={user}
      siteName={bootstrap.settings.siteName}
      siteTagline={bootstrap.settings.siteTagline}
      activeView={activeView}
      locale={locale}
      currency={currency}
      theme={theme}
      announcement={bootstrap.settings.announcement}
      onNavigate={(v) => setScreen(v)}
      onChangeLocale={handleChangeLocale}
      onChangeCurrency={handleChangeCurrency}
      onToggleTheme={() => setTheme((prev) => (prev === 'light' ? 'dark' : 'light'))}
      onLogout={handleLogout}
    >
      {activeView === 'dashboard' && (
        <NewOrderPage
          user={user}
          currency={currency}
          platforms={bootstrap.platforms}
          categories={bootstrap.categories}
          services={bootstrap.services}
          rankTiers={bootstrap.rankTiers}
          preselectedServiceId={preselectedServiceId}
          onUserUpdate={setUser}
          onGoOrders={() => setScreen('orders')}
        />
      )}

      {activeView === 'orders' && (
        <OrdersPage
          currency={currency}
          services={bootstrap.services}
          onUserUpdate={setUser}
          onNewOrder={() => setScreen('dashboard')}
        />
      )}

      {activeView === 'catalog' && (
        <ServicesCatalogPage
          currency={currency}
          platforms={bootstrap.platforms}
          categories={bootstrap.categories}
          services={bootstrap.services}
          onOrderService={handleOrderService}
        />
      )}

      {activeView === 'deposits' && (
        <DepositsPage user={user} currency={currency} onUserUpdate={setUser} />
      )}

      {activeView === 'transactions' && <TransactionsPage currency={currency} />}

      {activeView === 'affiliates' && (
        <AffiliatesPage currency={currency} onUserUpdate={setUser} />
      )}

      {activeView === 'statistics' && (
        <StatisticsPage user={user} currency={currency} rankTiers={bootstrap.rankTiers} />
      )}

      {activeView === 'api-docs' && (
        <ApiDocsPage
          user={user}
          latestRawApiKey={latestRawApiKey}
        />
      )}

      {activeView === 'articles' && <ArticlesPage posts={bootstrap.posts} />}

      {activeView === 'profile' && (
        <ProfilePage
          user={user}
          onUserUpdate={setUser}
          onRawApiKeyGenerated={setLatestRawApiKey}
        />
      )}

      {activeView === 'terms' && (
        <TermsPage siteName={bootstrap.settings.siteName} onBack={() => setScreen('dashboard')} />
      )}

      {activeView === 'admin' && user.role === 'admin' && (
        <AdminPage
          currency={currency}
          onRefreshBootstrap={async () => {
            await loadBootstrap();
          }}
        />
      )}
    </AppShell>
  );
}
