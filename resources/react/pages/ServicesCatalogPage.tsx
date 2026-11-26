import { useMemo, useState } from 'react';
import { ArrowLeft, Search, ShieldCheck } from 'lucide-react';
import type { Category, CurrencyCode, Platform, Service } from '../types/smm';
import { formatCurrency } from '../utils/pricing';

interface ServicesCatalogPageProps {
  currency: CurrencyCode;
  siteName?: string;
  platforms: Platform[];
  categories: Category[];
  services: Service[];
  isPublic?: boolean;
  onBackHome?: () => void;
  onOrderService: (serviceId: number) => void;
}

export function ServicesCatalogPage({
  currency,
  siteName,
  platforms,
  categories,
  services,
  isPublic = false,
  onBackHome,
  onOrderService,
}: ServicesCatalogPageProps) {
  const [platformId, setPlatformId] = useState<number>(0);
  const [search, setSearch] = useState('');

  const grouped = useMemo(() => {
    const q = search.trim().toLowerCase();
    return categories
      .filter((c) => (platformId === 0 ? true : c.platformId === platformId))
      .map((cat) => {
        const list = services.filter(
          (s) =>
            s.categoryId === cat.id &&
            (!q ||
              s.productCode.toLowerCase().includes(q) ||
              s.name.toLowerCase().includes(q) ||
              String(s.id) === q ||
              s.description.toLowerCase().includes(q))
        );
        return {
          category: cat,
          platform: platforms.find((p) => p.id === cat.platformId),
          services: list,
        };
      })
      .filter((g) => g.services.length > 0);
  }, [categories, platforms, services, platformId, search]);

  return (
    <div className={isPublic ? 'min-h-screen bg-[#f4f8fd] py-8 px-4 sm:px-6' : 'space-y-5'}>
      <div className={isPublic ? 'max-w-7xl mx-auto space-y-5' : 'space-y-5'}>
        {isPublic && onBackHome && (
          <button
            type="button"
            onClick={onBackHome}
            className="inline-flex items-center gap-1.5 text-sm font-bold text-slate-600 hover:text-blue-600 cursor-pointer"
          >
            <ArrowLeft className="w-4 h-4" /> กลับหน้าหลัก {siteName || ''}
          </button>
        )}

        <div className="nova-card p-5 flex flex-wrap items-center justify-between gap-4">
          <div>
            <h1 className="text-xl font-extrabold">รายการบริการทั้งหมด ({services.length})</h1>
            <p className="text-xs text-slate-500 mt-0.5">
              ราคาแสดงผลต่อ 1,000 หน่วย พร้อมความเร็วและสิทธิ์ Refill
            </p>
          </div>
          <div className="relative w-full sm:w-72">
            <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              type="search"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="ค้นหาชื่อบริการ หรือ รหัส..."
              className="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-[#dbe7f3] bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20"
            />
          </div>
        </div>

        <div className="flex flex-wrap gap-2">
          <button
            type="button"
            onClick={() => setPlatformId(0)}
            className={`px-3.5 py-2 rounded-xl text-xs font-bold border cursor-pointer ${
              platformId === 0
                ? 'bg-blue-600 text-white border-blue-600'
                : 'bg-white border-[#dbe7f3] text-slate-700'
            }`}
          >
            ทั้งหมด
          </button>
          {platforms.map((p) => (
            <button
              key={p.id}
              type="button"
              onClick={() => setPlatformId(p.id)}
              className={`px-3.5 py-2 rounded-xl text-xs font-bold border cursor-pointer ${
                platformId === p.id
                  ? 'bg-blue-600 text-white border-blue-600'
                  : 'bg-white border-[#dbe7f3] text-slate-700'
              }`}
            >
              {p.name}
            </button>
          ))}
        </div>

        {grouped.length === 0 ? (
          <div className="nova-card p-10 text-center text-sm text-slate-500">
            ไม่พบบริการที่ตรงกับคำค้นหา
          </div>
        ) : (
          grouped.map((group) => (
            <div key={group.category.id} className="nova-card overflow-hidden">
              <div className="px-5 py-3.5 bg-[#eaf2fb] border-b border-[#dbe7f3] flex items-center gap-2.5">
                {group.platform && (
                  <span
                    className="px-2 py-0.5 rounded text-[10px] font-black text-white"
                    style={{ backgroundColor: group.platform.badgeColor }}
                  >
                    {group.platform.code}
                  </span>
                )}
                <h2 className="text-sm font-extrabold text-[#1e3a5f]">{group.category.name}</h2>
              </div>
              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs">
                  <thead>
                    <tr className="border-b border-slate-100 text-slate-500 text-[11px] uppercase">
                      <th className="py-3 px-4">รหัสสินค้า</th>
                      <th className="py-3 px-4">ชื่อสินค้า / บริการ</th>
                      <th className="py-3 px-4">ราคา / 1,000</th>
                      <th className="py-3 px-4">ขั้นต่ำ – สูงสุด</th>
                      <th className="py-3 px-4">ความเร็ว</th>
                      <th className="py-3 px-4 text-right">สั่งซื้อ</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {group.services.map((svc) => (
                      <tr key={svc.id} className="hover:bg-[#f8fbff]">
                        <td className="py-3 px-4 font-mono whitespace-nowrap">
                          <div className="font-bold text-blue-700">{svc.productCode}</div>
                          <div className="text-[10px] text-slate-400">#{svc.id}</div>
                        </td>
                        <td className="py-3 px-4">
                          <div className="font-bold text-[#1e3a5f] flex items-center gap-2">
                            <span>{svc.name}</span>
                            {svc.refillSupport && (
                              <span className="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold">
                                <ShieldCheck className="w-3 h-3" /> R30
                              </span>
                            )}
                          </div>
                          <div className="text-[11px] text-slate-500 mt-0.5">{svc.description}</div>
                        </td>
                        <td className="py-3 px-4 font-extrabold text-blue-600 whitespace-nowrap">
                          {formatCurrency(svc.pricePer1k, currency)}
                        </td>
                        <td className="py-3 px-4 whitespace-nowrap text-slate-600">
                          {svc.minBuy.toLocaleString()} – {svc.maxBuy.toLocaleString()}
                        </td>
                        <td className="py-3 px-4 whitespace-nowrap text-slate-600">
                          {svc.speedLabel}
                        </td>
                        <td className="py-3 px-4 text-right whitespace-nowrap">
                          <button
                            type="button"
                            onClick={() => onOrderService(svc.id)}
                            className="px-3 py-1.5 rounded-lg text-xs font-bold text-white nova-gradient-bg hover:opacity-95 cursor-pointer"
                          >
                            สั่งซื้อ
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
}
