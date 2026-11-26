import type { CurrencyCode, RankTierConfig, Service, UserRank, UserRole, Voucher } from '../types/smm';
import {
  calculatePercentOf,
  calculatePerThousand,
  fromMicro,
  subDecimal,
  toMicro,
} from '../../server/src/utils/decimal';

export const DEFAULT_RANK_TIERS: RankTierConfig[] = [
  { rank: 'member', label: 'Member', discountPercent: 0, minSpentThreshold: 0 },
  { rank: 'silver', label: 'Silver', discountPercent: 3, minSpentThreshold: 1000 },
  { rank: 'gold', label: 'Gold', discountPercent: 5, minSpentThreshold: 3000 },
  { rank: 'platinum', label: 'Platinum', discountPercent: 8, minSpentThreshold: 10000 },
  { rank: 'agency', label: 'Agency', discountPercent: 12, minSpentThreshold: 30000 },
];

export const RANK_DISCOUNTS: Record<UserRank, number> = {
  member: 0,
  silver: 3,
  gold: 5,
  platinum: 8,
  agency: 12,
};

export interface CurrencyConfig {
  code: CurrencyCode;
  symbol: string;
  rateFromThb: number;
  decimals: number;
}

export const CURRENCIES: Record<CurrencyCode, CurrencyConfig> = {
  THB: { code: 'THB', symbol: '฿', rateFromThb: 1, decimals: 2 },
  USD: { code: 'USD', symbol: '$', rateFromThb: 1 / 35.5, decimals: 3 },
  VND: { code: 'VND', symbol: '₫', rateFromThb: 720, decimals: 0 },
};

export function parseLines(input: string): string[] {
  return String(input ?? '')
    .split(/\r?\n/)
    .map((line) => line.trim())
    .filter((line) => line.length > 0);
}

export function getDiscountByRank(
  rank: UserRank,
  customTiers?: RankTierConfig[]
): number {
  if (customTiers && customTiers.length > 0) {
    const found = customTiers.find((t) => t.rank === rank);
    if (found) return Math.max(0, Math.min(90, found.discountPercent));
  }
  return RANK_DISCOUNTS[rank] ?? 0;
}

export function convertCurrency(amountThb: number, currency: CurrencyCode = 'THB'): number {
  const cfg = CURRENCIES[currency] ?? CURRENCIES.THB;
  const safe = Number.isFinite(amountThb) ? amountThb : 0;
  return safe * cfg.rateFromThb;
}

export function formatCurrency(
  amountThb: number,
  currency: CurrencyCode = 'THB',
  includeApproxPrefix = true
): string {
  const safeAmount = Number.isFinite(amountThb) ? amountThb : 0;
  const cfg = CURRENCIES[currency] ?? CURRENCIES.THB;
  const converted = safeAmount * cfg.rateFromThb;
  const formatted = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: cfg.decimals,
    maximumFractionDigits: cfg.decimals,
  }).format(converted);

  const prefix = includeApproxPrefix && currency !== 'THB' ? '≈ ' : '';
  return currency === 'VND' ? `${prefix}${formatted} ${cfg.symbol}` : `${prefix}${cfg.symbol}${formatted}`;
}

export interface OrderPricingInput {
  service: Pick<
    Service,
    'id' | 'pricePer1k' | 'originalPricePer1k' | 'minBuy' | 'maxBuy' | 'type' | 'addType'
  >;
  rank: UserRank;
  role?: UserRole;
  customTiers?: RankTierConfig[];
  rawLinks: string;
  rawQuantity: number;
  rawComments?: string;
  isMultiple?: boolean;
}

export interface OrderPricingResult {
  valid: boolean;
  error?: string;
  links: string[];
  comments: string[];
  effectiveQuantity: number;
  subtotal: number;
  discountPercent: number;
  discountAmount: number;
  totalPayment: number;
  perOrderPayment: number;
  providerCostPerOrder: number;
  profitPerOrder: number;
}

export function calculateOrderPricing(input: OrderPricingInput): OrderPricingResult {
  const {
    service,
    rank,
    role,
    customTiers,
    rawLinks,
    rawQuantity,
    rawComments = '',
    isMultiple = false,
  } = input;

  const isAdminFree = role === 'admin';
  const discountPercent = isAdminFree ? 100 : getDiscountByRank(rank, customTiers);
  const links = isMultiple ? parseLines(rawLinks) : rawLinks.trim() ? [rawLinks.trim()] : [];

  if (links.length === 0) {
    return {
      valid: false,
      error: 'LINK_REQUIRED',
      links: [],
      comments: [],
      effectiveQuantity: 0,
      subtotal: 0,
      discountPercent,
      discountAmount: 0,
      totalPayment: 0,
      perOrderPayment: 0,
      providerCostPerOrder: 0,
      profitPerOrder: 0,
    };
  }

  let comments: string[] = [];
  let effectiveQuantity = Math.floor(Number(rawQuantity) || 0);

  if (service.type === 'custom_comments') {
    comments = parseLines(rawComments);
    effectiveQuantity = comments.length;
    if (effectiveQuantity === 0) {
      return {
        valid: false,
        error: 'COMMENTS_REQUIRED',
        links,
        comments: [],
        effectiveQuantity: 0,
        subtotal: 0,
        discountPercent,
        discountAmount: 0,
        totalPayment: 0,
        perOrderPayment: 0,
        providerCostPerOrder: 0,
        profitPerOrder: 0,
      };
    }
  }

  if (!Number.isFinite(effectiveQuantity) || effectiveQuantity <= 0) {
    return {
      valid: false,
      error: 'INVALID_QUANTITY',
      links,
      comments,
      effectiveQuantity: 0,
      subtotal: 0,
      discountPercent,
      discountAmount: 0,
      totalPayment: 0,
      perOrderPayment: 0,
      providerCostPerOrder: 0,
      profitPerOrder: 0,
    };
  }

  if (service.minBuy > 0 && effectiveQuantity < service.minBuy) {
    return {
      valid: false,
      error: `MIN_QUANTITY:${service.minBuy}`,
      links,
      comments,
      effectiveQuantity,
      subtotal: 0,
      discountPercent,
      discountAmount: 0,
      totalPayment: 0,
      perOrderPayment: 0,
      providerCostPerOrder: 0,
      profitPerOrder: 0,
    };
  }

  if (service.maxBuy > 0 && effectiveQuantity > service.maxBuy) {
    return {
      valid: false,
      error: `MAX_QUANTITY:${service.maxBuy}`,
      links,
      comments,
      effectiveQuantity,
      subtotal: 0,
      discountPercent,
      discountAmount: 0,
      totalPayment: 0,
      perOrderPayment: 0,
      providerCostPerOrder: 0,
      profitPerOrder: 0,
    };
  }

  const safePrice = Number.isFinite(service.pricePer1k) && service.pricePer1k >= 0 ? service.pricePer1k : 0;
  const safeOrig =
    Number.isFinite(service.originalPricePer1k) && service.originalPricePer1k > 0
      ? service.originalPricePer1k
      : 0;

  const perOrderSubtotal = calculatePerThousand(effectiveQuantity, safePrice);
  const perOrderDiscount = isAdminFree
    ? perOrderSubtotal
    : calculatePercentOf(perOrderSubtotal, discountPercent);
  const perOrderPayment = isAdminFree ? 0 : Math.max(0, subDecimal(perOrderSubtotal, perOrderDiscount));

  const subtotal = fromMicro(toMicro(perOrderSubtotal) * links.length);
  const discountAmount = isAdminFree ? subtotal : fromMicro(toMicro(perOrderDiscount) * links.length);
  const totalPayment = isAdminFree ? 0 : fromMicro(toMicro(perOrderPayment) * links.length);

  // Fixes original OrderController bug: never divide by originalPricePer1k
  const providerCostPerOrder =
    service.addType === 'api' && safeOrig > 0
      ? calculatePerThousand(effectiveQuantity, safeOrig)
      : 0;
  const profitPerOrder = subDecimal(perOrderPayment, providerCostPerOrder);

  return {
    valid: true,
    links,
    comments,
    effectiveQuantity,
    subtotal,
    discountPercent,
    discountAmount,
    totalPayment,
    perOrderPayment,
    providerCostPerOrder,
    profitPerOrder,
  };
}

export function validateVoucher(
  voucher: Voucher | undefined,
  username: string,
  userRank: UserRank = 'member',
  nowIso: string = new Date().toISOString()
): { valid: boolean; error?: string; value: number } {
  if (!voucher) {
    return { valid: false, error: 'VOUCHER_NOT_FOUND', value: 0 };
  }
  if (!voucher.status) {
    return { valid: false, error: 'VOUCHER_DISABLED', value: 0 };
  }
  if (voucher.expiresAt && new Date(voucher.expiresAt).getTime() < new Date(nowIso).getTime()) {
    return { valid: false, error: 'VOUCHER_EXPIRED', value: 0 };
  }
  if (voucher.maxUses > 0 && voucher.usedCount >= voucher.maxUses) {
    return { valid: false, error: 'VOUCHER_LIMIT_REACHED', value: 0 };
  }
  if (voucher.usedBy.includes(username)) {
    return { valid: false, error: 'VOUCHER_ALREADY_USED', value: 0 };
  }
  if (voucher.allowedRanks.length > 0 && !voucher.allowedRanks.includes(userRank)) {
    return { valid: false, error: 'VOUCHER_RANK_RESTRICTED', value: 0 };
  }
  return { valid: true, value: voucher.value };
}
