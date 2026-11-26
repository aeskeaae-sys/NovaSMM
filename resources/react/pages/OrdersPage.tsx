import { useEffect, useState } from 'react';
import { Eye, RefreshCw, RotateCcw, Search, X, XCircle } from 'lucide-react';
import type { CurrencyCode, Order, OrderStatus, Service, User } from '../types/smm';
import { formatCurrency } from '../utils/pricing';
import { apiRequest } from '../utils/api';
import { StatusBadge } from '../components/StatusBadge';

interface OrdersPageProps {
  currency: CurrencyCode;
  services: Service[];
  onUserUpdate: (u: User) => void;
  onNewOrder: () => void;
}

const STATUS_TABS: Array<{ key: string; label: string }> = [
  { key: 'ALL', label: 'ทั้งหมด' },
  { key: 'Pending', label: 'รอดำเนินการ' },
  { key: 'Processing', label: 'กำลังดำเนินการ' },
  { key: 'In Progress', label: 'กำลังส่งยอด' },
  { key: 'Completed', label: 'เสร็จสมบูรณ์' },
  { key: 'Partial', label: 'ส่งบางส่วน' },
  { key: 'Canceled', label: 'ยกเลิกแล้ว' },
  { key: 'Refunded', label: 'คืนเงินแล้ว' },
  { key: 'Failed', label: 'ล้มเหลว' },
];

export function OrdersPage({ currency, services, onUserUpdate, onNewOrder }: OrdersPageProps) {
  const [orders, setOrders] = useState<Order[]>([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [statusFilter, setStatusFilter] = useState<string>('ALL');
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const [busyId, setBusyId] = useState<number | null>(null);
  const [feedback, setFeedback] = useState('');
  const [detailOrder, setDetailOrder] = useState<Order | null>(null);

  const limit = 20;

  async function fetchOrders(targetPage = page) {
    setLoading(true);
    try {
      const qs = new URLSearchParams();
      if (statusFilter !== 'ALL') qs.set('status', statusFilter);
      if (search.trim()) qs.set('search', search.trim());
      qs.set('page', String(targetPage));
      qs.set('limit', String(limit));
      const res = await apiRequest<{ orders: Order[]; total: number }>(`/api/orders?${qs.toString()}`);
      setOrders(res.orders);
      setTotal(res.total);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    setPage(1);
    fetchOrders(1);
  }, [statusFilter]);

  async function handleSync(orderId: number) {
    setBusyId(orderId);
    setFeedback('');
    try {
      const res = await apiRequest<{ message: string }>(`/api/orders/${orderId}/sync`, {
        method: 'POST',
      });
      setFeedback(res.message);
      await fetchOrders(page);
    } catch (err) {
      setFeedback(err instanceof Error ? err.message : 'ซิงค์ไม่สำเร็จ');
    } finally {
      setBusyId(null);
    }
  }

  async function handleRefill(orderId: number) {
    setBusyId(orderId);
    setFeedback('');
    try {
      const res = await apiRequest<{ message: string }>(`/api/orders/${orderId}/refill`, {
        method: 'POST',
      });
      setFeedback(res.message);
      await fetchOrders(page);
    } catch (err) {
      setFeedback(err instanceof Error ? err.message : 'Refill ไม่สำเร็จ');
    } finally {
      setBusyId(null);
    }
  }

  async function handleCancel(orderId: number) {
    setBusyId(orderId);
    setFeedback('');
    try {
      const res = await apiRequest<{ message: string; user: User }>(`/api/orders/${orderId}/cancel`, {
        method: 'POST',
      });
      setFeedback(res.message);
      onUserUpdate(res.user);
      await fetchOrders(page);
    } catch (err) {
      setFeedback(err instanceof Error ? err.message : 'ยกเลิกไม่สำเร็จ');
    } finally {
      setBusyId(null);
    }
  }

  function canRefill(order: Order): boolean {
    const svc = services.find((s) => s.id === order.serviceId);
    return Boolean(svc?.refillSupport && order.status === 'Completed' && !order.refillRequested);
  }

  function canCancel(status: OrderStatus): boolean {
    return (
      status === 'Pending' ||
      status === 'Processing' ||
      status === 'In Progress' ||
      status === 'Partial'
    );
  }

  const totalPages = Math.max(1, Math.ceil(total / limit));

  return (
    <div className="space-y-5">
      <div className="nova-card p-5 flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-lg font-extrabold">จัดการและติดตามคำสั่งซื้อ</h1>
          <p className="text-xs text-slate-500">
            ตรวจสอบความคืบหน้า ซิงค์จาก Provider สั่งเติมยอด (Refill) หรือยกเลิกเพื่อคืนเงินเข้ากระเป๋า
          </p>
        </div>
        <div className="flex items-center gap-2">
          <button
            type="button"
            onClick={() => fetchOrders(page)}
            className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border border-[#dbe7f3] bg-white hover:bg-slate-50 cursor-pointer"
          >
            <RefreshCw className="w-3.5 h-3.5" /> รีเฟรช
          </button>
          <button
            type="button"
            onClick={onNewOrder}
            className="px-4 py-2 rounded-xl text-xs font-bold text-white nova-gradient-bg cursor-pointer"
          >
            + สร้างคำสั่งซื้อใหม่
          </button>
        </div>
      </div>

      {feedback && (
        <div className="p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold">
          {feedback}
        </div>
      )}

      <div className="nova-card p-5 space-y-4">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div className="flex flex-wrap gap-1.5">
            {STATUS_TABS.map((tab) => (
              <button
                key={tab.key}
                type="button"
                onClick={() => setStatusFilter(tab.key)}
                className={`px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer ${
                  statusFilter === tab.key
                    ? 'bg-blue-600 text-white'
                    : 'bg-[#f4f8fd] text-slate-600 hover:bg-blue-50'
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>

          <form
            onSubmit={(e) => {
              e.preventDefault();
              setPage(1);
              fetchOrders(1);
            }}
            className="flex items-center gap-2"
          >
            <div className="relative">
              <Search className="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                type="search"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="ค้นหาเลขคำสั่งซื้อ / ลิงก์..."
                className="pl-8 pr-3 py-2 text-xs rounded-xl border border-[#dbe7f3] bg-white w-56 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
              />
            </div>
            <button
              type="submit"
              className="px-3 py-2 rounded-xl text-xs font-bold bg-[#eaf2fb] text-blue-700 hover:bg-blue-100 cursor-pointer"
            >
              ค้นหา
            </button>
          </form>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse text-xs">
            <thead>
              <tr className="border-b border-[#dbe7f3] text-slate-500 uppercase text-[11px]">
                <th className="py-3 px-2.5">รหัส</th>
                <th className="py-3 px-2.5">บริการ / ลิงก์</th>
                <th className="py-3 px-2.5">ความคืบหน้า</th>
                <th className="py-3 px-2.5">ยอดชำระ</th>
                <th className="py-3 px-2.5">สถานะ</th>
                <th className="py-3 px-2.5 text-right">จัดการ</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {loading ? (
                <tr>
                  <td colSpan={6} className="py-10 text-center text-slate-400">
                    กำลังโหลดข้อมูล...
                  </td>
                </tr>
              ) : orders.length === 0 ? (
                <tr>
                  <td colSpan={6} className="py-10 text-center text-slate-400">
                    ไม่พบรายการคำสั่งซื้อ
                  </td>
                </tr>
              ) : (
                orders.map((order) => {
                  const pct =
                    order.quantity > 0
                      ? Math.min(100, Math.round((order.successCount / order.quantity) * 100))
                      : 0;
                  return (
                    <tr key={order.id} className="hover:bg-[#f8fbff]">
                      <td className="py-3 px-2.5 font-mono font-bold text-[#1e3a5f] whitespace-nowrap">
                        #{order.id}
                        <div className="text-[10px] font-normal text-slate-400">
                          {order.orderCode}
                        </div>
                      </td>
                      <td className="py-3 px-2.5 max-w-xs">
                        <div className="font-bold text-[#1e3a5f] truncate">{order.serviceName}</div>
                        <div className="text-slate-500 truncate mt-0.5">{order.objectId}</div>
                        <div className="text-[10px] text-slate-400 mt-0.5">
                          จำนวน: {order.quantity.toLocaleString()} · คงเหลือ:{' '}
                          {order.remains.toLocaleString()}
                        </div>
                      </td>
                      <td className="py-3 px-2.5 min-w-[130px]">
                        <div className="flex justify-between text-[11px] mb-1">
                          <span>
                            {order.successCount.toLocaleString()}/{order.quantity.toLocaleString()}
                          </span>
                          <span className="font-bold">{pct}%</span>
                        </div>
                        <div className="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                          <div
                            className="h-full rounded-full nova-gradient-bg transition-all"
                            style={{ width: `${pct}%` }}
                          />
                        </div>
                      </td>
                      <td className="py-3 px-2.5 font-bold whitespace-nowrap">
                        {formatCurrency(order.totalPayment, currency)}
                        {order.refundedAmount > 0 && (
                          <div className="text-[10px] text-emerald-600">
                            คืนแล้ว {formatCurrency(order.refundedAmount, currency)}
                          </div>
                        )}
                      </td>
                      <td className="py-3 px-2.5 whitespace-nowrap">
                        <StatusBadge status={order.status} />
                        {order.refillRequested && (
                          <div className="text-[10px] font-semibold text-emerald-600 mt-1">
                            Refill: {order.refillStatus}
                          </div>
                        )}
                      </td>
                      <td className="py-3 px-2.5 text-right whitespace-nowrap space-x-1.5">
                        <button
                          type="button"
                          onClick={() => setDetailOrder(order)}
                          className="inline-flex items-center gap-1 px-2 py-1.5 rounded-lg bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 cursor-pointer"
                        >
                          <Eye className="w-3 h-3" /> รายละเอียด
                        </button>
                        {order.providerOrderId && (
                          <button
                            type="button"
                            disabled={busyId === order.id}
                            onClick={() => handleSync(order.id)}
                            className="inline-flex items-center gap-1 px-2 py-1.5 rounded-lg bg-blue-50 text-blue-700 font-bold hover:bg-blue-100 cursor-pointer"
                          >
                            <RefreshCw className="w-3 h-3" /> Sync
                          </button>
                        )}
                        {canRefill(order) && (
                          <button
                            type="button"
                            disabled={busyId === order.id}
                            onClick={() => handleRefill(order.id)}
                            className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 font-bold hover:bg-emerald-100 cursor-pointer"
                          >
                            <RotateCcw className="w-3 h-3" /> Refill
                          </button>
                        )}
                        {canCancel(order.status) && (
                          <button
                            type="button"
                            disabled={busyId === order.id}
                            onClick={() => handleCancel(order.id)}
                            className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 font-bold hover:bg-rose-100 cursor-pointer"
                          >
                            <XCircle className="w-3 h-3" /> ยกเลิก/คืนเงิน
                          </button>
                        )}
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        <div className="flex items-center justify-between pt-2 text-xs text-slate-500">
          <span>
            ทั้งหมด {total} รายการ (หน้า {page}/{totalPages})
          </span>
          <div className="flex gap-2">
            <button
              type="button"
              disabled={page <= 1}
              onClick={() => {
                const next = page - 1;
                setPage(next);
                fetchOrders(next);
              }}
              className="px-3 py-1.5 rounded-lg border border-[#dbe7f3] disabled:opacity-40 cursor-pointer"
            >
              ก่อนหน้า
            </button>
            <button
              type="button"
              disabled={page >= totalPages}
              onClick={() => {
                const next = page + 1;
                setPage(next);
                fetchOrders(next);
              }}
              className="px-3 py-1.5 rounded-lg border border-[#dbe7f3] disabled:opacity-40 cursor-pointer"
            >
              ถัดไป
            </button>
          </div>
        </div>
      </div>

      {/* Order Details Modal */}
      {detailOrder && (
        <div role="dialog" aria-label="Order Details" className="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div className="fixed inset-0 bg-slate-900/45" onClick={() => setDetailOrder(null)} />
          <div className="relative z-10 w-full max-w-lg nova-card p-6 space-y-3 text-xs">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100">
              <h2 className="text-base font-extrabold">
                รายละเอียดคำสั่งซื้อ #{detailOrder.id} ({detailOrder.orderCode})
              </h2>
              <button
                type="button"
                aria-label="Close order details"
                onClick={() => setDetailOrder(null)}
                className="p-1 rounded-lg hover:bg-slate-100 cursor-pointer"
              >
                <X className="w-4 h-4" />
              </button>
            </div>
            <div className="space-y-2">
              <div><strong>บริการ:</strong> {detailOrder.serviceName}</div>
              <div className="break-all"><strong>ลิงก์เป้าหมาย:</strong> {detailOrder.objectId}</div>
              <div><strong>จำนวนสั่งซื้อ:</strong> {detailOrder.quantity.toLocaleString()} (ยอดเริ่ม {detailOrder.startNumber.toLocaleString()} · คงเหลือ {detailOrder.remains.toLocaleString()})</div>
              <div><strong>ยอดชำระ:</strong> {formatCurrency(detailOrder.totalPayment, currency)} (ส่วนลด {detailOrder.discountPercent}%)</div>
              {detailOrder.providerOrderId && (
                <div><strong>Provider Order ID:</strong> <span className="font-mono">{detailOrder.providerOrderId}</span></div>
              )}
              {detailOrder.providerError && (
                <div className="p-2 rounded bg-rose-50 text-rose-700">
                  <strong>Provider Notice:</strong> {detailOrder.providerError}
                </div>
              )}
              {detailOrder.comments.length > 0 && (
                <div>
                  <strong>คอมเมนต์ ({detailOrder.comments.length}):</strong>
                  <pre className="mt-1 p-2 rounded bg-[#f8fbff] border border-[#dbe7f3] max-h-28 overflow-y-auto">
                    {detailOrder.comments.join('\n')}
                  </pre>
                </div>
              )}
              {detailOrder.orderNote && <div><strong>หมายเหตุ:</strong> {detailOrder.orderNote}</div>}
              <div className="text-slate-400">
                สร้างเมื่อ: {new Date(detailOrder.createdAt).toLocaleString()} · อัปเดต: {new Date(detailOrder.updatedAt).toLocaleString()}
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
