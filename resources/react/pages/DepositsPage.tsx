import { useEffect, useState, type FormEvent } from 'react';
import { CheckCircle2, Clock, Copy, CreditCard, Gift, Landmark, QrCode } from 'lucide-react';
import type { BankAccount, CurrencyCode, DepositInvoice, User } from '../types/smm';
import { formatCurrency } from '../utils/pricing';
import { apiRequest } from '../utils/api';

interface DepositsPageProps {
  user: User;
  currency: CurrencyCode;
  onUserUpdate: (u: User) => void;
}

export function DepositsPage({ user, currency, onUserUpdate }: DepositsPageProps) {
  const [banks, setBanks] = useState<BankAccount[]>([]);
  const [invoices, setInvoices] = useState<DepositInvoice[]>([]);
  const [selectedBankId, setSelectedBankId] = useState<number>(1);
  const [method, setMethod] = useState<'bank' | 'card' | 'crypto' | 'paypal'>('bank');
  const [amount, setAmount] = useState<number>(500);
  const [proofNote, setProofNote] = useState('');
  const [voucherCode, setVoucherCode] = useState('');
  const [busy, setBusy] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [copiedText, setCopiedText] = useState('');

  async function loadDeposits() {
    const res = await apiRequest<{ banks: BankAccount[]; invoices: DepositInvoice[] }>('/api/deposits');
    setBanks(res.banks);
    setInvoices(res.invoices);
    if (res.banks[0]) setSelectedBankId(res.banks[0].id);
  }

  useEffect(() => {
    loadDeposits();
  }, []);

  async function handleCreateInvoice(e: FormEvent) {
    e.preventDefault();
    setBusy(true);
    setMessage('');
    setError('');
    try {
      const idempotencyKey =
        typeof crypto !== 'undefined' && crypto.randomUUID
          ? crypto.randomUUID()
          : `dep-${Date.now()}-${Math.random().toString(36).slice(2)}`;
      const res = await apiRequest<{ message: string; user: User }>('/api/deposits/invoice', {
        method: 'POST',
        body: JSON.stringify({
          method,
          amount,
          bankId: selectedBankId,
          proofNote,
          idempotencyKey,
        }),
      });
      setMessage(res.message);
      setProofNote('');
      onUserUpdate(res.user);
      await loadDeposits();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'สร้างรายการเติมเงินไม่สำเร็จ');
    } finally {
      setBusy(false);
    }
  }

  async function handleRedeemVoucher(e: FormEvent) {
    e.preventDefault();
    setBusy(true);
    setMessage('');
    setError('');
    try {
      const res = await apiRequest<{ message: string; user: User }>('/api/deposits/voucher', {
        method: 'POST',
        body: JSON.stringify({ code: voucherCode }),
      });
      setMessage(res.message);
      setVoucherCode('');
      onUserUpdate(res.user);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'แลกโค้ดไม่สำเร็จ');
    } finally {
      setBusy(false);
    }
  }

  function copyValue(val: string) {
    navigator.clipboard?.writeText(val);
    setCopiedText(val);
    setTimeout(() => setCopiedText(''), 1800);
  }

  const activeBank = banks.find((b) => b.id === selectedBankId) ?? banks[0];

  return (
    <div className="space-y-5">
      <div className="nova-card p-5 flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-lg font-extrabold">เติมเงินเข้ากระเป๋า NovaSMM</h1>
          <p className="text-xs text-slate-500">
            ยอดเงินปัจจุบัน: <strong className="text-blue-600">{formatCurrency(user.balance, currency)}</strong>
          </p>
        </div>
      </div>

      {message && (
        <div className="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
          <CheckCircle2 className="w-4 h-4 shrink-0" />
          <span>{message}</span>
        </div>
      )}
      {error && (
        <div role="alert" className="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
          {error}
        </div>
      )}

      <div className="grid lg:grid-cols-12 gap-5 items-start">
        {/* Left: Deposit Methods */}
        <div className="lg:col-span-7 nova-card p-6 space-y-5">
          <div className="flex flex-wrap gap-2">
            {[
              { id: 'bank' as const, label: 'โอนธนาคาร / PromptPay' },
              { id: 'crypto' as const, label: 'USDT (TRC20)' },
              { id: 'paypal' as const, label: 'PayPal' },
              { id: 'card' as const, label: 'บัตรเติมเงิน' },
            ].map((tab) => (
              <button
                key={tab.id}
                type="button"
                onClick={() => setMethod(tab.id)}
                className={`px-3.5 py-2 rounded-xl text-xs font-bold border transition cursor-pointer ${
                  method === tab.id
                    ? 'bg-blue-600 text-white border-blue-600'
                    : 'bg-[#f4f8fd] text-slate-700 border-[#dbe7f3]'
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>

          <form onSubmit={handleCreateInvoice} className="space-y-4">
            {method === 'bank' && banks.length === 0 && (
              <div className="p-4 rounded-xl bg-slate-50 border border-[#dbe7f3] text-xs text-slate-600">
                ผู้ดูแลระบบยังไม่ได้ตั้งค่าบัญชีธนาคารสำหรับโอนเงิน กรุณาเพิ่มบัญชีธนาคารในหน้า Admin Control Center หรือเลือกช่องทางชำระเงินอื่น
              </div>
            )}

            {method === 'bank' && banks.length > 0 && (
              <div className="space-y-3">
                <div className="grid sm:grid-cols-3 gap-2.5">
                  {banks.map((b) => (
                    <button
                      key={b.id}
                      type="button"
                      onClick={() => setSelectedBankId(b.id)}
                      className={`p-3 rounded-xl border text-left transition cursor-pointer ${
                        selectedBankId === b.id
                          ? 'bg-blue-50 border-blue-500'
                          : 'bg-white border-[#dbe7f3] hover:bg-slate-50'
                      }`}
                    >
                      <div className="flex items-center gap-1.5 text-xs font-extrabold text-[#1e3a5f]">
                        <Landmark className="w-3.5 h-3.5 text-blue-600" />
                        <span>{b.shortName}</span>
                      </div>
                      <div className="text-[11px] text-slate-500 mt-1 truncate">{b.accountNumber}</div>
                    </button>
                  ))}
                </div>

                {activeBank && (
                  <div className="p-4 rounded-2xl bg-[#f4f8fd] border border-[#dbe7f3] flex flex-col sm:flex-row items-center gap-4">
                    <div className="w-24 h-24 rounded-xl bg-white border border-[#dbe7f3] grid place-items-center shrink-0">
                      <QrCode className="w-16 h-16 text-[#1e3a5f]" />
                    </div>
                    <div className="flex-1 text-xs space-y-1.5 w-full">
                      <div className="font-extrabold text-sm">{activeBank.bankName}</div>
                      <div className="text-slate-600">ชื่อบัญชี: {activeBank.accountName}</div>
                      <div className="flex items-center gap-2">
                        <span className="font-mono font-bold text-blue-700 text-sm">
                          {activeBank.accountNumber}
                        </span>
                        <button
                          type="button"
                          onClick={() => copyValue(activeBank.accountNumber)}
                          className="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-white border border-[#dbe7f3] text-[11px] font-semibold cursor-pointer"
                        >
                          <Copy className="w-3 h-3" />
                          {copiedText === activeBank.accountNumber ? 'คัดลอกแล้ว' : 'คัดลอก'}
                        </button>
                      </div>
                    </div>
                  </div>
                )}
              </div>
            )}

            <div>
              <label htmlFor="deposit-amount" className="block text-xs font-bold text-slate-700 mb-1.5">
                จำนวนเงินที่ต้องการเติม (THB)
              </label>
              <div className="flex flex-wrap gap-2 mb-2">
                {[100, 300, 500, 1000, 5000].map((preset) => (
                  <button
                    key={preset}
                    type="button"
                    onClick={() => setAmount(preset)}
                    className={`px-3 py-1.5 rounded-lg text-xs font-bold border cursor-pointer ${
                      amount === preset
                        ? 'bg-blue-600 text-white border-blue-600'
                        : 'bg-white border-[#dbe7f3] text-slate-700'
                    }`}
                  >
                    ฿{preset.toLocaleString()}
                  </button>
                ))}
              </div>
              <input
                id="deposit-amount"
                type="number"
                min={20}
                required
                value={amount}
                onChange={(e) => setAmount(Number(e.target.value))}
                className="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white"
              />
            </div>

            <div>
              <label htmlFor="deposit-proof" className="block text-xs font-bold text-slate-700 mb-1.5">
                เลขอ้างอิงสลิป / เวลาที่โอน (เพื่อความรวดเร็วในการตรวจสอบ)
              </label>
              <input
                id="deposit-proof"
                type="text"
                value={proofNote}
                onChange={(e) => setProofNote(e.target.value)}
                placeholder="เช่น โอนเวลา 14:25 จากบัญชีลงท้าย 1234"
                className="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white"
              />
            </div>

            <div className="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-start gap-2">
              <Clock className="w-4 h-4 shrink-0 mt-0.5" />
              <span>
                ใบแจ้งหนี้ใหม่จะอยู่ในสถานะ <strong>รอตรวจสอบ (Pending)</strong> ยอดเงินจะเข้ากระเป๋าหลังจากระบบ Payment Gateway ยืนยัน Webhook หรือผู้ดูแลระบบตรวจสอบสลิปและอนุมัติแล้วเท่านั้น
              </span>
            </div>

            <button
              type="submit"
              disabled={busy || (method === 'bank' && banks.length === 0)}
              className="w-full py-3 rounded-xl text-sm font-bold text-white nova-gradient-bg shadow-md shadow-blue-600/20 hover:opacity-95 disabled:opacity-60 transition cursor-pointer flex items-center justify-center gap-2"
            >
              <CreditCard className="w-4 h-4" />
              <span>ส่งแจ้งโอน / สร้างใบแจ้งหนี้</span>
            </button>
          </form>
        </div>

        {/* Right: Voucher Redeem + Invoices */}
        <div className="lg:col-span-5 space-y-5">
          <form onSubmit={handleRedeemVoucher} className="nova-card p-6 space-y-4">
            <div className="flex items-center gap-2 font-extrabold text-base">
              <Gift className="w-5 h-5 text-blue-600" />
              <span>แลกโค้ดของขวัญ (Voucher)</span>
            </div>
            <p className="text-xs text-slate-500">
              กรอกรหัส Voucher ที่ได้รับจากผู้ดูแลระบบหรือแคมเปญโปรโมชั่นเพื่อรับเครดิตเข้ากระเป๋า
            </p>
            <div>
              <label htmlFor="voucher-code" className="sr-only">
                Voucher Code
              </label>
              <input
                id="voucher-code"
                type="text"
                required
                value={voucherCode}
                onChange={(e) => setVoucherCode(e.target.value)}
                placeholder="กรอกรหัส Voucher"
                className="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#dbe7f3] bg-white uppercase font-mono"
              />
            </div>
            <button
              type="submit"
              disabled={busy}
              className="w-full py-2.5 rounded-xl text-xs font-bold bg-[#eaf2fb] text-blue-700 hover:bg-blue-100 transition cursor-pointer"
            >
              แลกรับเครดิตฟรี
            </button>
          </form>

          <div className="nova-card p-5 space-y-3">
            <h2 className="text-sm font-extrabold">รายการใบแจ้งหนี้ล่าสุด</h2>
            <div className="space-y-2 max-h-72 overflow-y-auto">
              {invoices.length === 0 ? (
                <div className="text-xs text-slate-400 py-4 text-center">ยังไม่มีรายการฝากเงิน</div>
              ) : (
                invoices.map((inv) => (
                  <div
                    key={inv.id}
                    className="p-3 rounded-xl border border-[#dbe7f3] bg-[#f8fbff] flex items-center justify-between gap-2 text-xs"
                  >
                    <div>
                      <div className="font-bold font-mono">{inv.code}</div>
                      <div className="text-slate-500">
                        {inv.channelName} · {formatCurrency(inv.netAmount, currency)}
                      </div>
                      {inv.proofNote && <div className="text-[11px] text-slate-400">{inv.proofNote}</div>}
                    </div>
                    {inv.status === 'paid' ? (
                      <span className="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold">
                        อนุมัติแล้ว
                      </span>
                    ) : inv.status === 'cancelled' ? (
                      <span className="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-bold">
                        ถูกปฏิเสธ
                      </span>
                    ) : (
                      <span className="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 font-bold">
                        รอตรวจสอบ
                      </span>
                    )}
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
