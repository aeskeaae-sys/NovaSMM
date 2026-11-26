import { useState } from 'react';
import { ArrowLeft, Code2, Play } from 'lucide-react';
import type { User } from '../types/smm';

interface ApiDocsPageProps {
  user: User | null;
  siteName?: string;
  latestRawApiKey?: string | null;
  isPublic?: boolean;
  onBackHome?: () => void;
}

export function ApiDocsPage({
  user,
  siteName,
  latestRawApiKey = null,
  isPublic = false,
  onBackHome,
}: ApiDocsPageProps) {
  const defaultTesterKey = latestRawApiKey || '';

  const [apiKey, setApiKey] = useState(defaultTesterKey);
  const [action, setAction] = useState<'balance' | 'services' | 'add' | 'status' | 'refill' | 'cancel'>('balance');
  const [serviceId, setServiceId] = useState('1');
  const [link, setLink] = useState('https://www.tiktok.com/@username/video/1');
  const [quantity, setQuantity] = useState('100');
  const [orderId, setOrderId] = useState('');
  const [output, setOutput] = useState('');
  const [testing, setTesting] = useState(false);

  const origin = typeof window !== 'undefined' ? window.location.origin : 'https://novasmm.app';
  const endpoint = `${origin}/api/v2`;

  async function runLiveTest() {
    setTesting(true);
    setOutput('');
    try {
      const payload: Record<string, string> = { key: apiKey, action };
      if (action === 'add') {
        payload.service = serviceId;
        payload.link = link;
        payload.quantity = quantity;
      } else if (action === 'status' || action === 'refill' || action === 'cancel') {
        payload.order = orderId;
      }
      const res = await fetch('/api/v2', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const json = await res.json();
      setOutput(JSON.stringify(json, null, 2));
    } catch (err) {
      setOutput(err instanceof Error ? err.message : 'Request failed');
    } finally {
      setTesting(false);
    }
  }

  return (
    <div className={isPublic ? 'min-h-screen bg-[#f4f8fd] py-8 px-4 sm:px-6' : 'space-y-5'}>
      <div className={isPublic ? 'max-w-6xl mx-auto space-y-5' : 'space-y-5'}>
        {isPublic && onBackHome && (
          <button
            type="button"
            onClick={onBackHome}
            className="inline-flex items-center gap-1.5 text-sm font-bold text-slate-600 hover:text-blue-600 cursor-pointer"
          >
            <ArrowLeft className="w-4 h-4" /> กลับหน้าหลัก {siteName || ''}
          </button>
        )}

        <div className="nova-card p-6">
          <div className="flex items-center gap-2.5 mb-2">
            <Code2 className="w-6 h-6 text-blue-600" />
            <h1 className="text-xl font-extrabold">SMM Panel API v2 Documentation</h1>
          </div>
          <p className="text-xs text-slate-600">
            มาตรฐาน API สำหรับเชื่อมต่อเว็บตัวแทนจำหน่าย (Child Panel) หรือสคริปต์สั่งซื้ออัตโนมัติ
          </p>

          <div className="mt-4 grid sm:grid-cols-2 gap-3 text-xs">
            <div className="p-3.5 rounded-xl bg-[#f4f8fd] border border-[#dbe7f3]">
              <div className="text-slate-500 font-semibold">API Endpoint (POST / GET)</div>
              <div className="font-mono font-bold text-blue-700 mt-1 break-all">{endpoint}</div>
            </div>
            <div className="p-3.5 rounded-xl bg-[#f4f8fd] border border-[#dbe7f3]">
              <div className="text-slate-500 font-semibold">Your API Key Status</div>
              <div className="font-mono font-bold text-[#1e3a5f] mt-1 truncate">
                {user
                  ? user.hasApiKey
                    ? `${user.apiKeyPrefix} (Active)`
                    : 'ยังไม่มีคีย์ — กดสร้างคีย์ในหน้าโปรไฟล์'
                  : 'เข้าสู่ระบบเพื่อจัดการ API Key'}
              </div>
            </div>
          </div>
        </div>

        {/* Live Interactive API v2 Playground */}
        <div className="nova-card p-6 space-y-4">
          <h2 className="text-base font-extrabold">ทดสอบเรียกใช้งาน API v2 จริง (Live Tester)</h2>
          <div className="grid sm:grid-cols-2 gap-3 text-xs">
            <div>
              <label htmlFor="v2-key" className="block font-bold mb-1">
                API Key
              </label>
              <input
                id="v2-key"
                type="text"
                value={apiKey}
                onChange={(e) => setApiKey(e.target.value)}
                placeholder="nv_live_..."
                className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3] font-mono"
              />
            </div>
            <div>
              <label htmlFor="v2-action" className="block font-bold mb-1">
                Action
              </label>
              <select
                id="v2-action"
                value={action}
                onChange={(e) =>
                  setAction(
                    e.target.value as 'balance' | 'services' | 'add' | 'status' | 'refill' | 'cancel'
                  )
                }
                className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3] bg-white"
              >
                <option value="balance">balance — ตรวจสอบยอดเงิน</option>
                <option value="services">services — ดึงรายการบริการทั้งหมด</option>
                <option value="add">add — สั่งซื้อบริการใหม่</option>
                <option value="status">status — เช็กสถานะคำสั่งซื้อ</option>
                <option value="refill">refill — ขอเติมยอดชดเชย</option>
                <option value="cancel">cancel — ยกเลิกและคืนเงิน</option>
              </select>
            </div>
          </div>

          {action === 'add' && (
            <div className="grid sm:grid-cols-3 gap-3 text-xs">
              <div>
                <label className="block font-bold mb-1">Service ID</label>
                <input
                  type="text"
                  value={serviceId}
                  onChange={(e) => setServiceId(e.target.value)}
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
                />
              </div>
              <div>
                <label className="block font-bold mb-1">Link</label>
                <input
                  type="text"
                  value={link}
                  onChange={(e) => setLink(e.target.value)}
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
                />
              </div>
              <div>
                <label className="block font-bold mb-1">Quantity</label>
                <input
                  type="text"
                  value={quantity}
                  onChange={(e) => setQuantity(e.target.value)}
                  className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
                />
              </div>
            </div>
          )}

          {(action === 'status' || action === 'refill' || action === 'cancel') && (
            <div className="max-w-xs text-xs">
              <label className="block font-bold mb-1">Order ID</label>
              <input
                type="text"
                value={orderId}
                onChange={(e) => setOrderId(e.target.value)}
                placeholder="ระบุเลขคำสั่งซื้อ เช่น 1"
                className="w-full px-3 py-2 rounded-xl border border-[#dbe7f3]"
              />
            </div>
          )}

          <button
            type="button"
            disabled={testing}
            onClick={runLiveTest}
            className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white nova-gradient-bg cursor-pointer"
          >
            <Play className="w-3.5 h-3.5" />
            <span>{testing ? 'กำลังส่งคำขอ...' : 'ยิง Request ไปที่ /api/v2'}</span>
          </button>

          {output && (
            <pre className="p-4 rounded-xl bg-slate-900 text-sky-300 text-xs overflow-x-auto max-h-72">
              {output}
            </pre>
          )}
        </div>

        {/* Reference Specs */}
        <div className="grid md:grid-cols-2 gap-4 text-xs">
          <div className="nova-card p-5 space-y-2">
            <h3 className="font-extrabold text-sm">1. ดึงรายการบริการ (services)</h3>
            <pre className="p-3 rounded-xl bg-slate-900 text-slate-100 overflow-x-auto">
{`POST ${endpoint}
{
  "key": "<YOUR_API_KEY>",
  "action": "services"
}`}
            </pre>
          </div>
          <div className="nova-card p-5 space-y-2">
            <h3 className="font-extrabold text-sm">2. สั่งซื้อบริการ (add)</h3>
            <pre className="p-3 rounded-xl bg-slate-900 text-slate-100 overflow-x-auto">
{`POST ${endpoint}
{
  "key": "<YOUR_API_KEY>",
  "action": "add",
  "service": 1,
  "link": "https://tiktok.com/@username",
  "quantity": 1000
}`}
            </pre>
          </div>
        </div>
      </div>
    </div>
  );
}
