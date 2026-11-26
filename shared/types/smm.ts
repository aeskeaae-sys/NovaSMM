export type UserRole = 'user' | 'admin';
export type UserRank = 'member' | 'silver' | 'gold' | 'platinum' | 'agency';
export type UserStatus = 'active' | 'locked';
export type CurrencyCode = 'THB' | 'USD' | 'VND';
export type LocaleCode = 'th' | 'en' | 'vi';

export type OrderStatus =
  | 'Pending'
  | 'Processing'
  | 'In Progress'
  | 'Completed'
  | 'Partial'
  | 'Canceled'
  | 'Refunded'
  | 'Failed';

export type ServiceType = 'default' | 'custom_comments' | 'reactions' | 'vip_like';

export interface RankTierConfig {
  rank: UserRank;
  label: string;
  discountPercent: number;
  minSpentThreshold: number;
}

export interface User {
  id: number;
  name: string;
  username: string;
  email: string;
  role: UserRole;
  rank: UserRank;
  status: UserStatus;
  balance: number;
  totalDeposit: number;
  totalSpent: number;
  affiliateBalance: number;
  affiliateCode: string;
  referredBy: string | null;
  apiKeyPrefix: string; // masked preview e.g. nv_live_...a1b2
  hasApiKey: boolean;
  currency: CurrencyCode;
  language: LocaleCode;
  createdAt: string;
  lastLoginAt: string;
}

export interface Platform {
  id: number;
  name: string;
  slug: string;
  code: string;
  badgeColor: string;
  status: boolean;
  priority: number;
}

export interface Category {
  id: number;
  platformId: number;
  name: string;
  slug: string;
  status: boolean;
  priority: number;
}

export interface Service {
  id: number;
  productCode: string;
  categoryId: number;
  platformId: number;
  name: string;
  pricePer1k: number;
  originalPricePer1k: number;
  minBuy: number;
  maxBuy: number;
  type: ServiceType;
  addType: 'manual' | 'api';
  apiProviderId: number | null;
  apiServiceId: string | null;
  refillSupport: boolean;
  cancelSupport: boolean;
  speedLabel: string;
  averageTime: string;
  description: string;
  status: boolean;
}

export interface Order {
  id: number;
  orderCode: string;
  userId: number;
  username: string;
  serviceId: number;
  categoryId: number;
  platformId: number;
  serviceName: string;
  objectId: string;
  quantity: number;
  startNumber: number;
  remains: number;
  successCount: number;
  pricePer1k: number;
  discountPercent: number;
  totalPayment: number;
  providerCost: number;
  providerId: number | null;
  providerOrderId: string | null;
  providerError: string | null;
  status: OrderStatus;
  orderNote: string;
  comments: string[];
  reactionType?: string;
  refillRequested: boolean;
  refillStatus: 'none' | 'pending' | 'completed' | 'rejected';
  refundedAmount: number;
  createdAt: string;
  updatedAt: string;
}

export interface Transaction {
  id: number;
  code: string;
  userId: number;
  username: string;
  type:
    | 'deposit'
    | 'new-order'
    | 'refund'
    | 'gift-code'
    | 'affiliate'
    | 'admin-adjust'
    | 'welcome-bonus';
  amount: number;
  balanceBefore: number;
  balanceAfter: number;
  status: 'paid' | 'pending' | 'failed';
  content: string;
  idempotencyKey?: string | null;
  createdAt: string;
}

export interface DepositInvoice {
  id: number;
  code: string;
  userId: number;
  username: string;
  method: 'bank' | 'card' | 'crypto' | 'paypal' | 'perfect_money';
  amount: number;
  fee: number;
  netAmount: number;
  channelName: string;
  reference: string;
  proofNote: string;
  status: 'pending' | 'paid' | 'cancelled';
  reviewedBy: string | null;
  createdAt: string;
  paidAt: string | null;
}

export interface BankAccount {
  id: number;
  bankName: string;
  shortName: string;
  accountName: string;
  accountNumber: string;
  promptPayId: string;
  minDeposit: number;
  status: boolean;
}

export interface Voucher {
  id: number;
  code: string;
  value: number;
  maxUses: number;
  usedCount: number;
  usedBy: string[];
  allowedRanks: UserRank[];
  expiresAt: string;
  status: boolean;
}

export interface ApiProvider {
  id: number;
  name: string;
  url: string;
  hasApiKey: boolean;
  apiKeyMasked: string;
  balance: number;
  currency: string;
  priceIncreasePercent: number;
  autoSync: boolean;
  status: boolean;
  lastSyncAt: string | null;
  lastError: string | null;
}

export interface ProviderServiceItem {
  id: number;
  providerId: number;
  externalServiceId: string;
  name: string;
  categoryName: string;
  rate: number;
  minBuy: number;
  maxBuy: number;
  refillSupport: boolean;
  cancelSupport: boolean;
  syncedAt: string;
}

export interface ProviderLog {
  id: number;
  providerId: number;
  providerName: string;
  action: string;
  orderId: number | null;
  httpStatus: number | null;
  success: boolean;
  message: string;
  createdAt: string;
}

export interface NotificationItem {
  id: number;
  userId: number;
  title: string;
  message: string;
  type: 'order' | 'deposit' | 'system' | 'voucher';
  read: boolean;
  createdAt: string;
}

export interface Announcement {
  id: number;
  title: string;
  content: string;
  level: 'info' | 'promo' | 'warning';
  active: boolean;
  createdAt: string;
}

export interface AuditLog {
  id: number;
  actorUserId: number | null;
  actorUsername: string;
  action: string;
  targetType: string;
  targetId: string;
  reason: string;
  metadata: string;
  ipAddress: string;
  createdAt: string;
}

export interface Post {
  id: number;
  slug: string;
  title: string;
  excerpt: string;
  content: string;
  category: string;
  status: boolean;
  createdAt: string;
}

export interface SiteSettings {
  siteName: string;
  siteTagline: string;
  siteDescription: string;
  announcement: string;
  affiliateCommissionPercent: number;
  minAffiliateWithdraw: number;
  welcomeBonusAmount: number;
  depositMethods: {
    bank: boolean;
    card: boolean;
    crypto: boolean;
    paypal: boolean;
    perfect_money: boolean;
  };
  contactFacebook: string;
  contactTelegram: string;
  contactLine: string;
}
