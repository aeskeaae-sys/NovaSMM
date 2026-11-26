import type { OrderStatus } from '../types/smm';

const STATUS_STYLE: Record<OrderStatus, { label: string; cls: string }> = {
  Pending: { label: 'รอดำเนินการ', cls: 'bg-amber-50 text-amber-700 border-amber-200' },
  Processing: { label: 'กำลังดำเนินการ', cls: 'bg-sky-50 text-sky-700 border-sky-200' },
  'In Progress': { label: 'กำลังส่งยอด', cls: 'bg-blue-50 text-blue-700 border-blue-200' },
  Completed: { label: 'เสร็จสมบูรณ์', cls: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
  Partial: { label: 'ส่งบางส่วน', cls: 'bg-indigo-50 text-indigo-700 border-indigo-200' },
  Canceled: { label: 'ยกเลิกแล้ว', cls: 'bg-slate-100 text-slate-700 border-slate-300' },
  Refunded: { label: 'คืนเงินแล้ว', cls: 'bg-orange-50 text-orange-700 border-orange-200' },
  Failed: { label: 'ล้มเหลว', cls: 'bg-rose-50 text-rose-700 border-rose-200' },
};

export function StatusBadge({ status }: { status: OrderStatus }) {
  const cfg = STATUS_STYLE[status] ?? {
    label: status,
    cls: 'bg-slate-100 text-slate-700 border-slate-200',
  };
  return (
    <span className={`inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-md border ${cfg.cls}`}>
      {cfg.label}
    </span>
  );
}
