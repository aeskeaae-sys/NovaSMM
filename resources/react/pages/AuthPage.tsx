import { useState, type FormEvent } from 'react';
import { ArrowLeft, KeyRound, Lock, Mail, ShieldCheck, User as UserIcon } from 'lucide-react';
import type { LocaleCode, User } from '../types/smm';
import { apiRequest } from '../utils/api';

interface AuthPageProps {
  mode: 'login' | 'register' | 'setup-admin';
  locale: LocaleCode;
  siteName: string;
  siteTagline?: string;
  needsAdminSetup: boolean;
  initialRefCode?: string;
  onSuccess: (user: User) => void;
  onSwitchMode: (mode: 'login' | 'register' | 'setup-admin') => void;
  onBackHome: () => void;
}

export function AuthPage({
  mode,
  siteName,
  siteTagline = '',
  needsAdminSetup,
  initialRefCode = '',
  onSuccess,
  onSwitchMode,
  onBackHome,
}: AuthPageProps) {
  const [subMode, setSubMode] = useState<'default' | 'forgot' | 'reset'>('default');
  const [storeNameInput, setStoreNameInput] = useState(siteName === 'NovaSMM' ? '' : siteName);
  const [storeTaglineInput, setStoreTaglineInput] = useState(siteTagline);
  const [identifier, setIdentifier] = useState('');
  const [username, setUsername] = useState('');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [refCode, setRefCode] = useState(initialRefCode);
  const [resetToken, setResetToken] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [infoMsg, setInfoMsg] = useState('');

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setError('');
    setInfoMsg('');
    setLoading(true);
    try {
      if (subMode === 'forgot') {
        const res = await apiRequest<{ message: string }>('/api/auth/forgot-password', {
          method: 'POST',
          body: JSON.stringify({ email }),
        });
        setInfoMsg(res.message);
        setSubMode('reset');
        return;
      }

      if (subMode === 'reset') {
        const res = await apiRequest<{ message: string }>('/api/auth/reset-password', {
          method: 'POST',
          body: JSON.stringify({ token: resetToken, newPassword }),
        });
        setInfoMsg(res.message);
        setSubMode('default');
        onSwitchMode('login');
        return;
      }

      if (mode === 'setup-admin') {
        const res = await apiRequest<{ user: User }>('/api/auth/setup-admin', {
          method: 'POST',
          body: JSON.stringify({
            name,
            username,
            email,
            password,
            siteName: storeNameInput,
            siteTagline: storeTaglineInput,
          }),
        });
        onSuccess(res.user);
      } else if (mode === 'login') {
        const res = await apiRequest<{ user: User }>('/api/auth/login', {
          method: 'POST',
          body: JSON.stringify({ identifier, password }),
        });
        onSuccess(res.user);
      } else {
        const res = await apiRequest<{ user: User }>('/api/auth/register', {
          method: 'POST',
          body: JSON.stringify({ name, username, email, password, ref: refCode }),
        });
        onSuccess(res.user);
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'เกิดข้อผิดพลาด');
    } finally {
      setLoading(false);
    }
  }

  const title =
    subMode === 'forgot'
      ? 'ลืมรหัสผ่าน'
      : subMode === 'reset'
        ? 'ตั้งรหัสผ่านใหม่'
        : mode === 'setup-admin'
          ? 'ตั้งค่าร้านค้า & ผู้ดูแลระบบ'
          : mode === 'login'
            ? `เข้าสู่ระบบ ${siteName}`
            : `สมัครสมาชิก ${siteName}`;

  return (
    <div className="min-h-screen bg-[#f4f8fd] flex flex-col items-center justify-center p-4">
      <div className="w-full max-w-md">
        <button
          type="button"
          onClick={onBackHome}
          className="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-blue-600 mb-5 cursor-pointer"
        >
          <ArrowLeft className="w-4 h-4" /> กลับหน้าหลัก
        </button>

        <div className="nova-card p-7">
          <div className="flex items-center gap-2.5 mb-6">
            <img src="/assets/images/brand-logos/nova-icon.svg" alt={siteName} className="w-10 h-10" />
            <div>
              <h1 className="text-xl font-extrabold nova-gradient-text">{title}</h1>
              <p className="text-xs text-slate-500">
                {subMode !== 'default'
                  ? 'กู้คืนสิทธิ์การเข้าถึงบัญชีอย่างปลอดภัยด้วย Reset Token'
                  : mode === 'setup-admin'
                    ? 'สร้างบัญชีเจ้าของระบบ (Administrator) เพื่อเริ่มตั้งค่าเว็บไซต์จริง'
                    : mode === 'login'
                      ? 'จัดการคำสั่งซื้อ เติมเงิน และใช้งาน API ได้ในที่เดียว'
                      : 'สมัครสมาชิกเพื่อเริ่มต้นสั่งซื้อบริการอัตโนมัติ 24/7'}
              </p>
            </div>
          </div>

          {needsAdminSetup && mode !== 'setup-admin' && subMode === 'default' && (
            <div className="mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center justify-between gap-2">
              <span className="flex items-center gap-1.5 font-semibold">
                <ShieldCheck className="w-4 h-4 text-amber-600 shrink-0" />
                ยังไม่มีบัญชีผู้ดูแลระบบในฐานข้อมูล
              </span>
              <button
                type="button"
                onClick={() => {
                  setError('');
                  onSwitchMode('setup-admin');
                }}
                className="px-2.5 py-1 rounded-lg bg-amber-600 text-white font-bold shrink-0 cursor-pointer"
              >
                ตั้งค่า Admin
              </button>
            </div>
          )}

          {error && (
            <div
              role="alert"
              className="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium"
            >
              {error}
            </div>
          )}

          {infoMsg && (
            <div className="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium">
              {infoMsg}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            {subMode === 'forgot' && (
              <>
                <div>
                  <label htmlFor="forgot-email" className="block text-xs font-bold text-slate-700 mb-1.5">
                    อีเมลที่ใช้สมัครสมาชิก
                  </label>
                  <input
                    id="forgot-email"
                    type="email"
                    required
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="you@example.com"
                    className="w-full px-3 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white"
                  />
                </div>
                <button
                  type="button"
                  onClick={() => setSubMode('reset')}
                  className="text-[11px] font-bold text-blue-600 hover:underline cursor-pointer"
                >
                  มี Reset Token แล้ว? กรอกโทเคนเพื่อตั้งรหัสผ่านใหม่
                </button>
              </>
            )}

            {subMode === 'reset' && (
              <>
                <div>
                  <label htmlFor="reset-token" className="block text-xs font-bold text-slate-700 mb-1.5">
                    Reset Token (จากอีเมลหรือผู้ดูแลระบบ)
                  </label>
                  <input
                    id="reset-token"
                    type="text"
                    required
                    value={resetToken}
                    onChange={(e) => setResetToken(e.target.value)}
                    placeholder="nvr_..."
                    className="w-full px-3 py-2.5 text-sm font-mono rounded-xl border border-[#dbe7f3] bg-white"
                  />
                </div>
                <div>
                  <label htmlFor="reset-password" className="block text-xs font-bold text-slate-700 mb-1.5">
                    รหัสผ่านใหม่ (6 ตัวอักษรขึ้นไป)
                  </label>
                  <input
                    id="reset-password"
                    type="password"
                    required
                    minLength={6}
                    value={newPassword}
                    onChange={(e) => setNewPassword(e.target.value)}
                    placeholder="••••••••"
                    className="w-full px-3 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white"
                  />
                </div>
              </>
            )}

            {subMode === 'default' && (
              <>
                {mode === 'login' ? (
                  <div>
                    <label htmlFor="auth-identifier" className="block text-xs font-bold text-slate-700 mb-1.5">
                      อีเมลหรือชื่อผู้ใช้
                    </label>
                    <div className="relative">
                      <UserIcon className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <input
                        id="auth-identifier"
                        type="text"
                        required
                        value={identifier}
                        onChange={(e) => setIdentifier(e.target.value)}
                        placeholder="อีเมลหรือชื่อผู้ใช้"
                        className="w-full pl-9 pr-3 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                      />
                    </div>
                  </div>
                ) : (
                  <>
                    {mode === 'setup-admin' && (
                      <div className="p-3.5 rounded-xl bg-[#f4f8fd] border border-[#dbe7f3] space-y-2.5">
                        <div>
                          <label htmlFor="setup-site-name" className="block text-xs font-bold text-slate-700 mb-1">
                            ชื่อร้านค้า / เว็บไซต์ของคุณ
                          </label>
                          <input
                            id="setup-site-name"
                            type="text"
                            value={storeNameInput}
                            onChange={(e) => setStoreNameInput(e.target.value)}
                            placeholder="เช่น AuraBoost Store"
                            className="w-full px-3 py-2 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                          />
                        </div>
                        <div>
                          <label htmlFor="setup-site-tagline" className="block text-xs font-bold text-slate-700 mb-1">
                            สโลแกนร้าน (ไม่บังคับ)
                          </label>
                          <input
                            id="setup-site-tagline"
                            type="text"
                            value={storeTaglineInput}
                            onChange={(e) => setStoreTaglineInput(e.target.value)}
                            placeholder="เช่น ศูนย์รวมสินค้าดิจิทัล & บริการโซเชียล 24/7"
                            className="w-full px-3 py-2 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                          />
                        </div>
                      </div>
                    )}
                    <div className="grid grid-cols-2 gap-3">
                      <div>
                        <label htmlFor="reg-username" className="block text-xs font-bold text-slate-700 mb-1.5">
                          ชื่อผู้ใช้ (Username)
                        </label>
                        <input
                          id="reg-username"
                          type="text"
                          required
                          value={username}
                          onChange={(e) => setUsername(e.target.value)}
                          placeholder={mode === 'setup-admin' ? 'admin' : 'username'}
                          className="w-full px-3 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                        />
                      </div>
                      <div>
                        <label htmlFor="reg-name" className="block text-xs font-bold text-slate-700 mb-1.5">
                          ชื่อแสดงผล
                        </label>
                        <input
                          id="reg-name"
                          type="text"
                          value={name}
                          onChange={(e) => setName(e.target.value)}
                          placeholder={mode === 'setup-admin' ? 'System Owner' : 'ชื่อของคุณ'}
                          className="w-full px-3 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                        />
                      </div>
                    </div>
                    <div>
                      <label htmlFor="reg-email" className="block text-xs font-bold text-slate-700 mb-1.5">
                        อีเมล
                      </label>
                      <div className="relative">
                        <Mail className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input
                          id="reg-email"
                          type="email"
                          required
                          value={email}
                          onChange={(e) => setEmail(e.target.value)}
                          placeholder="you@example.com"
                          className="w-full pl-9 pr-3 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                        />
                      </div>
                    </div>
                  </>
                )}

                <div>
                  <div className="flex items-center justify-between mb-1.5">
                    <label htmlFor="auth-password" className="block text-xs font-bold text-slate-700">
                      รหัสผ่าน
                    </label>
                    {mode === 'login' && (
                      <button
                        type="button"
                        onClick={() => {
                          setSubMode('forgot');
                          setError('');
                        }}
                        className="text-[11px] font-semibold text-blue-600 hover:underline cursor-pointer"
                      >
                        ลืมรหัสผ่าน?
                      </button>
                    )}
                  </div>
                  <div className="relative">
                    <Lock className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                      id="auth-password"
                      type="password"
                      required
                      minLength={6}
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      placeholder="••••••••"
                      className="w-full pl-9 pr-3 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                    />
                  </div>
                </div>

                {mode === 'register' && (
                  <div>
                    <label htmlFor="reg-ref" className="block text-xs font-bold text-slate-700 mb-1.5">
                      รหัสแนะนำเพื่อน (ไม่บังคับ)
                    </label>
                    <div className="relative">
                      <KeyRound className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <input
                        id="reg-ref"
                        type="text"
                        value={refCode}
                        onChange={(e) => setRefCode(e.target.value)}
                        placeholder="กรอกรหัส Affiliate (ถ้ามี)"
                        className="w-full pl-9 pr-3 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                      />
                    </div>
                  </div>
                )}
              </>
            )}

            <button
              type="submit"
              disabled={loading}
              className="w-full py-3 rounded-xl text-sm font-bold text-white nova-gradient-bg shadow-md shadow-blue-600/20 hover:opacity-95 disabled:opacity-60 transition cursor-pointer"
            >
              {loading
                ? 'กำลังดำเนินการ...'
                : subMode === 'forgot'
                  ? 'ส่งคำขอรีเซ็ตรหัสผ่าน'
                  : subMode === 'reset'
                    ? 'ยืนยันรหัสผ่านใหม่'
                    : mode === 'setup-admin'
                      ? 'สร้างบัญชีผู้ดูแลระบบ'
                      : mode === 'login'
                        ? 'เข้าสู่ระบบ'
                        : 'สร้างบัญชีสมาชิก'}
            </button>

            {subMode !== 'default' && (
              <button
                type="button"
                onClick={() => {
                  setSubMode('default');
                  setError('');
                }}
                className="w-full py-2 text-xs font-bold text-slate-500 hover:text-blue-600 cursor-pointer"
              >
                กลับไปหน้าเข้าสู่ระบบ
              </button>
            )}
          </form>

          <div className="mt-5 text-center text-xs text-slate-600">
            {mode === 'login' ? (
              <>
                ยังไม่มีบัญชีใช่ไหม?{' '}
                <button
                  type="button"
                  onClick={() => {
                    setSubMode('default');
                    onSwitchMode('register');
                  }}
                  className="font-bold text-blue-600 hover:underline cursor-pointer"
                >
                  สมัครสมาชิกฟรี
                </button>
              </>
            ) : (
              <>
                มีบัญชีอยู่แล้ว?{' '}
                <button
                  type="button"
                  onClick={() => {
                    setSubMode('default');
                    onSwitchMode('login');
                  }}
                  className="font-bold text-blue-600 hover:underline cursor-pointer"
                >
                  เข้าสู่ระบบ
                </button>
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
