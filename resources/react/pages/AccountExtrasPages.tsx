import { useEffect, useState, type FormEvent } from 'react';
import { ArrowLeft, BookOpen, Copy, KeyRound, Lock, ShieldAlert, Users, Wallet } from 'lucide-react';
import type { CurrencyCode, Order, Post, RankTierConfig, Transaction, User } from '../types/smm';
import { formatCurrency, getDiscountByRank } from '../utils/pricing';
import { apiRequest } from '../utils/api';

// ---- Transactions Page ----
export function TransactionsPage({ currency }: { currency: CurrencyCode }) {
  const [items, setItems] = useState<Transaction[]>([]);
  const [type, setType] = useState('ALL');

  useEffect(() => {
    apiRequest<{ transactions: Transaction[] }>(`/api/transactions?type=${type}`)
      .then((res) => setItems(res.transactions))
      .catch(() => {});
  }, [type]);

  return (
    <div className="space-y-5">
      <div className="nova-card p-5 flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-lg font-extrabold">ประวัติธุรกรรมทางการเงิน</h1>
          <p className="text-xs text-slate-500">รายการเติมเงิน คำสั่งซื้อ คืนเงิน และแลกโค้ดทั้งหมด</p>
        </div>
        <select
          aria-label="Filter transaction type"
          value={type}
          onChange={(e) => setType(e.target.value)}
          className="text-xs font-bold px-3 py-2 rounded-xl border border-[#dbe7f3] bg-white"
        >
          <option value="ALL">ทุกประเภท</option>
          <option value="deposit">เติมเงิน (Deposit)</option>
          <option value="new-order">สั่งซื้อบริการ (Order)</option>
          <option value="refund">คืนเงิน (Refund)</option>
          <option value="gift-code">โค้ดของขวัญ (Voucher)</option>
          <option value="affiliate">ค่าแนะนำ (Affiliate)</option>
          <option value="welcome-bonus">โบนัสต้อนรับ (Welcome)</option>
          <option value="admin-adjust">ปรับยอดโดยแอดมิน</option>
        </select>
      </div>

      <div className="nova-card overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="border-b border-[#dbe7f3] bg-[#f8fbff] text-slate-500 uppercase text-[11px]">
                <th className="py-3 px-4">รหัสธุรกรรม</th>
                <th className="py-3 px-4">รายละเอียด</th>
                <th className="py-3 px-4">จำนวนเงิน</th>
                <th className="py-3 px-4">คงเหลือหลังทำรายการ</th>
                <th className="py-3 px-4">วันที่</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {items.map((tx) => (
                <tr key={tx.id} className="hover:bg-[#f8fbff]">
                  <td className="py-3 px-4 font-mono font-bold">{tx.code}</td>
                  <td className="py-3 px-4">{tx.content}</td>
                  <td
                    className={`py-3 px-4 font-extrabold ${
                      tx.amount >= 0 ? 'text-emerald-600' : 'text-rose-600'
                    }`}
                  >
                    {tx.amount >= 0 ? '+' : ''}
                    {formatCurrency(tx.amount, currency)}
                  </td>
                  <td className="py-3 px-4 font-bold">{formatCurrency(tx.balanceAfter, currency)}</td>
                  <td className="py-3 px-4 text-slate-500">
                    {new Date(tx.createdAt).toLocaleString()}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

// ---- Affiliates Page ----
export function AffiliatesPage({
  currency,
  onUserUpdate,
}: {
  currency: CurrencyCode;
  onUserUpdate: (u: User) => void;
}) {
  const [data, setData] = useState<{
    affiliateCode: string;
    affiliateBalance: number;
    commissionPercent: number;
    minWithdraw: number;
    referrals: Array<{ id: number; username: string; totalDeposit: number; createdAt: string }>;
  } | null>(null);
  const [msg, setMsg] = useState('');
  const [err, setErr] = useState('');

  async function load() {
    const res = await apiRequest<typeof data>('/api/affiliates');
    setData(res);
  }

  useEffect(() => {
    load();
  }, []);

  async function withdraw() {
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string; user: User }>('/api/affiliates/withdraw', {
        method: 'POST',
      });
      setMsg(res.message);
      onUserUpdate(res.user);
      await load();
    } catch (e) {
      setErr(e instanceof Error ? e.message : 'ถอนไม่สำเร็จ');
    }
  }

  const refUrl =
    typeof window !== 'undefined' && data
      ? `${window.location.origin}/?ref=${data.affiliateCode}`
      : '';

  return (
    <div className="space-y-5">
      <div className="nova-card p-6 grid sm:grid-cols-3 gap-4 items-center">
        <div>
          <div className="text-xs text-slate-500 font-semibold">รายได้ Affiliate คงเหลือ</div>
          <div className="text-2xl font-extrabold text-blue-600 mt-1">
            {formatCurrency(data?.affiliateBalance ?? 0, currency)}
          </div>
          <div className="text-[11px] text-slate-500 mt-1">
            รับค่าคอมมิชชัน {data?.commissionPercent ?? 5}% ทุกครั้งที่เพื่อนเติมเงินสำเร็จ
          </div>
        </div>
        <div className="sm:col-span-2 space-y-2">
          <div className="text-xs font-bold text-slate-700">ลิงก์แนะนำเพื่อนของคุณ</div>
          <div className="flex flex-wrap gap-2">
            <input
              readOnly
              value={refUrl}
              className="flex-1 min-w-[200px] px-3 py-2 text-xs font-mono rounded-xl border border-[#dbe7f3] bg-[#f8fbff]"
            />
            <button
              type="button"
              onClick={() => navigator.clipboard?.writeText(refUrl)}
              className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#eaf2fb] text-blue-700 cursor-pointer"
            >
              <Copy className="w-3.5 h-3.5" /> คัดลอก
            </button>
            <button
              type="button"
              onClick={withdraw}
              className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white nova-gradient-bg cursor-pointer"
            >
              <Wallet className="w-3.5 h-3.5" /> โอนเข้ากระเป๋าหลัก
            </button>
          </div>
          {msg && <div className="text-xs font-bold text-emerald-600">{msg}</div>}
          {err && <div className="text-xs font-bold text-rose-600">{err}</div>}
        </div>
      </div>

      <div className="nova-card p-5">
        <h2 className="text-sm font-extrabold flex items-center gap-2 mb-3">
          <Users className="w-4 h-4 text-blue-600" /> สมาชิกที่สมัครผ่านลิงก์ของคุณ (
          {data?.referrals.length ?? 0})
        </h2>
        {data && data.referrals.length > 0 ? (
          <div className="space-y-2 text-xs">
            {data.referrals.map((r) => (
              <div
                key={r.id}
                className="p-3 rounded-xl border border-[#dbe7f3] flex items-center justify-between"
              >
                <span className="font-bold">{r.username}</span>
                <span className="text-slate-500">
                  ยอดเติมรวม {formatCurrency(r.totalDeposit, currency)}
                </span>
              </div>
            ))}
          </div>
        ) : (
          <div className="text-xs text-slate-400 py-6 text-center">
            ยังไม่มีผู้สมัครผ่านลิงก์แนะนำของคุณ แชร์ลิงก์ด้านบนเพื่อเริ่มรับรายได้!
          </div>
        )}
      </div>
    </div>
  );
}

// ---- Statistics Page ----
export function StatisticsPage({
  user,
  currency,
  rankTiers,
}: {
  user: User;
  currency: CurrencyCode;
  rankTiers: RankTierConfig[];
}) {
  const [orders, setOrders] = useState<Order[]>([]);
  useEffect(() => {
    apiRequest<{ orders: Order[] }>('/api/orders?limit=200')
      .then((res) => setOrders(res.orders))
      .catch(() => {});
  }, []);

  const completed = orders.filter((o) => o.status === 'Completed').length;
  const processing = orders.filter(
    (o) => o.status === 'Processing' || o.status === 'Pending' || o.status === 'In Progress'
  ).length;
  const refunded = orders.filter(
    (o) => o.status === 'Refunded' || o.status === 'Canceled'
  ).length;

  return (
    <div className="space-y-5">
      <div className="nova-card p-5">
        <h1 className="text-lg font-extrabold">สถิติบัญชีและการใช้งาน</h1>
        <p className="text-xs text-slate-500">ภาพรวมคำสั่งซื้อและระดับสิทธิประโยชน์ของคุณ</p>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="nova-card p-5">
          <div className="text-xs text-slate-500">คำสั่งซื้อทั้งหมด</div>
          <div className="text-2xl font-extrabold mt-1">{orders.length}</div>
        </div>
        <div className="nova-card p-5">
          <div className="text-xs text-slate-500">เสร็จสมบูรณ์</div>
          <div className="text-2xl font-extrabold text-emerald-600 mt-1">{completed}</div>
        </div>
        <div className="nova-card p-5">
          <div className="text-xs text-slate-500">กำลังดำเนินการ</div>
          <div className="text-2xl font-extrabold text-blue-600 mt-1">{processing}</div>
        </div>
        <div className="nova-card p-5">
          <div className="text-xs text-slate-500">ยกเลิก / คืนเงิน</div>
          <div className="text-2xl font-extrabold text-slate-600 mt-1">{refunded}</div>
        </div>
      </div>

      <div className="nova-card p-6 space-y-3">
        <h2 className="text-sm font-extrabold">ระดับสมาชิกและส่วนลดอัตโนมัติ (Rank Tiers)</h2>
        <div className="grid sm:grid-cols-5 gap-3 text-xs">
          {rankTiers.map((tier) => (
            <div
              key={tier.rank}
              className={`p-3.5 rounded-xl border ${
                user.rank === tier.rank
                  ? 'bg-blue-50 border-blue-500 text-blue-900'
                  : 'bg-[#f8fbff] border-[#dbe7f3] text-slate-600'
              }`}
            >
              <div className="font-extrabold uppercase">{tier.label}</div>
              <div className="text-lg font-black mt-1">
                ลด {getDiscountByRank(tier.rank, rankTiers)}%
              </div>
              <div className="text-[11px] text-slate-500 mt-0.5">
                ยอดสะสม ≥ {formatCurrency(tier.minSpentThreshold, currency)}
              </div>
              {user.rank === tier.rank && (
                <span className="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded bg-blue-600 text-white">
                  ระดับปัจจุบัน
                </span>
              )}
            </div>
          ))}
        </div>
        <div className="text-xs text-slate-500 pt-1">
          ยอดใช้จ่ายสะสมปัจจุบัน: <strong>{formatCurrency(user.totalSpent, currency)}</strong>
        </div>
      </div>
    </div>
  );
}

// ---- Articles Page ----
export function ArticlesPage({ posts }: { posts: Post[] }) {
  const [selected, setSelected] = useState<Post | null>(posts[0] ?? null);
  return (
    <div className="grid lg:grid-cols-12 gap-5">
      <div className="lg:col-span-5 space-y-3">
        <h1 className="text-lg font-extrabold px-1 flex items-center gap-2">
          <BookOpen className="w-5 h-5 text-blue-600" /> ข่าวสารและคู่มือการใช้งาน
        </h1>
        {posts.map((post) => (
          <button
            key={post.id}
            type="button"
            onClick={() => setSelected(post)}
            className={`w-full text-left nova-card p-4 transition cursor-pointer ${
              selected?.id === post.id ? 'border-blue-500 ring-2 ring-blue-500/15' : ''
            }`}
          >
            <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-[#eaf2fb] text-blue-700">
              {post.category}
            </span>
            <h2 className="text-sm font-extrabold mt-2">{post.title}</h2>
            <p className="text-xs text-slate-500 mt-1 line-clamp-2">{post.excerpt}</p>
          </button>
        ))}
      </div>
      <div className="lg:col-span-7">
        {selected && (
          <article className="nova-card p-6 space-y-3">
            <span className="text-xs font-bold px-2.5 py-1 rounded bg-[#eaf2fb] text-blue-700">
              {selected.category}
            </span>
            <h2 className="text-xl font-extrabold">{selected.title}</h2>
            <div className="text-xs text-slate-400">
              อัปเดตเมื่อ {new Date(selected.createdAt).toLocaleDateString()}
            </div>
            <p className="text-sm text-slate-700 leading-relaxed pt-3 border-t border-slate-100">
              {selected.content}
            </p>
          </article>
        )}
      </div>
    </div>
  );
}

// ---- Profile Page ----
export function ProfilePage({
  user,
  onUserUpdate,
  onRawApiKeyGenerated,
}: {
  user: User;
  onUserUpdate: (u: User) => void;
  onRawApiKeyGenerated: (rawKey: string | null) => void;
}) {
  const [name, setName] = useState(user.name);
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [revealedKey, setRevealedKey] = useState<string>('');
  const [msg, setMsg] = useState('');
  const [err, setErr] = useState('');

  async function saveProfile(e: FormEvent) {
    e.preventDefault();
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string; user: User }>('/api/profile/update', {
        method: 'POST',
        body: JSON.stringify({ name }),
      });
      onUserUpdate(res.user);
      setMsg(res.message);
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'อัปเดตไม่สำเร็จ');
    }
  }

  async function regenerateToken() {
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string; user: User; rawApiKey: string }>(
        '/api/profile/token',
        { method: 'POST' }
      );
      onUserUpdate(res.user);
      setRevealedKey(res.rawApiKey);
      onRawApiKeyGenerated(res.rawApiKey);
      setMsg(res.message);
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'สร้างคีย์ใหม่ไม่สำเร็จ');
    }
  }

  async function revokeToken() {
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string; user: User }>('/api/profile/token/revoke', {
        method: 'POST',
      });
      onUserUpdate(res.user);
      setRevealedKey('');
      onRawApiKeyGenerated(null);
      setMsg(res.message);
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'เพิกถอนคีย์ไม่สำเร็จ');
    }
  }

  async function changePassword(e: FormEvent) {
    e.preventDefault();
    setMsg('');
    setErr('');
    try {
      const res = await apiRequest<{ message: string }>('/api/profile/password', {
        method: 'POST',
        body: JSON.stringify({ currentPassword, newPassword }),
      });
      setCurrentPassword('');
      setNewPassword('');
      setMsg(res.message);
    } catch (e2) {
      setErr(e2 instanceof Error ? e2.message : 'เปลี่ยนรหัสผ่านไม่สำเร็จ');
    }
  }

  return (
    <div className="space-y-5 max-w-4xl">
      {msg && <div className="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">{msg}</div>}
      {err && <div className="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold">{err}</div>}

      <div className="grid md:grid-cols-2 gap-5">
        <form onSubmit={saveProfile} className="nova-card p-6 space-y-4">
          <h2 className="text-base font-extrabold">ข้อมูลส่วนตัว</h2>
          <div className="text-xs space-y-3">
            <div>
              <label className="block font-bold mb-1">ชื่อแสดงผล</label>
              <input
                type="text"
                value={name}
                onChange={(e) => setName(e.target.value)}
                className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
              />
            </div>
            <div>
              <label className="block font-bold mb-1">Username</label>
              <input
                readOnly
                value={user.username}
                className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3] bg-slate-50 text-slate-500"
              />
            </div>
            <div>
              <label className="block font-bold mb-1">อีเมล</label>
              <input
                readOnly
                value={user.email}
                className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3] bg-slate-50 text-slate-500"
              />
            </div>
          </div>
          <button
            type="submit"
            className="px-4 py-2.5 rounded-xl text-xs font-bold text-white nova-gradient-bg cursor-pointer"
          >
            บันทึกข้อมูล
          </button>
        </form>

        <form onSubmit={changePassword} className="nova-card p-6 space-y-4">
          <h2 className="text-base font-extrabold flex items-center gap-2">
            <Lock className="w-4 h-4 text-blue-600" /> เปลี่ยนรหัสผ่าน
          </h2>
          <div className="text-xs space-y-3">
            <div>
              <label className="block font-bold mb-1">รหัสผ่านปัจจุบัน</label>
              <input
                type="password"
                required
                value={currentPassword}
                onChange={(e) => setCurrentPassword(e.target.value)}
                className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
              />
            </div>
            <div>
              <label className="block font-bold mb-1">รหัสผ่านใหม่</label>
              <input
                type="password"
                required
                minLength={6}
                value={newPassword}
                onChange={(e) => setNewPassword(e.target.value)}
                className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
              />
            </div>
          </div>
          <button
            type="submit"
            className="px-4 py-2.5 rounded-xl text-xs font-bold bg-[#eaf2fb] text-blue-700 hover:bg-blue-100 cursor-pointer"
          >
            อัปเดตรหัสผ่าน
          </button>
        </form>
      </div>

      <div className="nova-card p-6 space-y-3">
        <h2 className="text-base font-extrabold flex items-center gap-2">
          <KeyRound className="w-4 h-4 text-blue-600" /> API Key ส่วนตัว (Hashed at Rest)
        </h2>
        <p className="text-xs text-slate-500">
          เพื่อความปลอดภัย ระบบเก็บรหัส API Key ด้วย SHA-256 Hash และจะแสดงคีย์เต็มเพียงครั้งเดียวเมื่อกดสร้างคีย์ใหม่
        </p>

        {revealedKey && (
          <div className="p-3.5 rounded-xl bg-amber-50 border border-amber-300 text-xs space-y-2">
            <div className="font-bold text-amber-900">
              API Key ใหม่ของคุณ (คัดลอกเก็บไว้ตอนนี้ ระบบจะไม่แสดงคีย์เต็มอีก):
            </div>
            <div className="flex items-center gap-2">
              <code className="flex-1 px-3 py-2 rounded-lg bg-white border border-amber-200 font-mono text-slate-900 break-all">
                {revealedKey}
              </code>
              <button
                type="button"
                onClick={() => navigator.clipboard?.writeText(revealedKey)}
                className="px-3 py-2 rounded-lg bg-amber-600 text-white font-bold cursor-pointer"
              >
                คัดลอก
              </button>
            </div>
          </div>
        )}

        <div className="flex flex-wrap items-center gap-2">
          <input
            readOnly
            value={user.hasApiKey ? user.apiKeyPrefix : 'ยังไม่มีคีย์ที่ใช้งาน (หรือถูกเพิกถอนแล้ว)'}
            className="flex-1 min-w-[220px] px-3 py-2 text-xs font-mono rounded-xl border border-[#dbe7f3] bg-[#f8fbff]"
          />
          <button
            type="button"
            onClick={regenerateToken}
            className="px-3.5 py-2 rounded-xl text-xs font-bold bg-blue-600 text-white cursor-pointer"
          >
            สร้างคีย์ใหม่
          </button>
          {user.hasApiKey && (
            <button
              type="button"
              onClick={revokeToken}
              className="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 cursor-pointer"
            >
              <ShieldAlert className="w-3.5 h-3.5" /> เพิกถอนคีย์
            </button>
          )}
        </div>
      </div>
    </div>
  );
}

// ---- Terms Page ----
export function TermsPage({ siteName = 'ร้านค้า', onBack }: { siteName?: string; onBack: () => void }) {
  return (
    <div className="min-h-screen bg-[#f4f8fd] py-10 px-4 sm:px-6">
      <div className="max-w-3xl mx-auto space-y-5">
        <button
          type="button"
          onClick={onBack}
          className="inline-flex items-center gap-1.5 text-sm font-bold text-slate-600 hover:text-blue-600 cursor-pointer"
        >
          <ArrowLeft className="w-4 h-4" /> กลับหน้าหลัก
        </button>
        <div className="nova-card p-7 space-y-4 text-sm text-slate-700 leading-relaxed">
          <h1 className="text-2xl font-extrabold text-[#1e3a5f]">ข้อกำหนดและเงื่อนไขการใช้บริการ {siteName}</h1>
          <p>1. ผู้ใช้งานต้องตั้งค่าบัญชีหรือโพสต์เป้าหมายเป็นสาธารณะ (Public) ก่อนสั่งซื้อบริการทุกครั้ง</p>
          <p>2. ห้ามสั่งซื้อบริการซ้ำซ้อนบนลิงก์เดียวกันขณะที่คำสั่งซื้อเดิมยังไม่เสร็จสิ้น</p>
          <p>3. การเติมเงินเข้าสู่ระบบใช้สำหรับสั่งซื้อบริการภายในแพลตฟอร์ม หากคำสั่งซื้อถูกยกเลิก ระบบจะคืนเครดิตเข้ากระเป๋าเงินอัตโนมัติ</p>
          <p>4. ห้ามใช้งานระบบเพื่อเผยแพร่เนื้อหาที่ผิดกฎหมายหรือละเมิดสิทธิ์ผู้อื่น</p>
        </div>
      </div>
    </div>
  );
}
