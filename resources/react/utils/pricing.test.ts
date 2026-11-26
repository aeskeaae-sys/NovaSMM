import test from 'node:test';
import assert from 'node:assert/strict';
import {
  calculateOrderPricing,
  formatCurrency,
  getDiscountByRank,
  parseLines,
  validateVoucher,
} from './pricing';
import { addDecimal, subDecimal } from '../../server/src/utils/decimal';
import type { Voucher } from '../types/smm';

const sampleService = {
  id: 101,
  pricePer1k: 100,
  originalPricePer1k: 60,
  minBuy: 100,
  maxBuy: 50000,
  type: 'default' as const,
  addType: 'api' as const,
};

test('fixed-point decimal arithmetic avoids IEEE-754 float drift', () => {
  assert.equal(addDecimal(0.1, 0.2), 0.3);
  assert.equal(subDecimal(1500, 61.75), 1438.25);
});

test('rank discount returns expected percentages', () => {
  assert.equal(getDiscountByRank('member'), 0);
  assert.equal(getDiscountByRank('gold'), 5);
  assert.equal(getDiscountByRank('agency'), 12);
});

test('calculateOrderPricing computes subtotal, discount, profit, and provider cost accurately', () => {
  const res = calculateOrderPricing({
    service: sampleService,
    rank: 'gold',
    rawLinks: 'https://tiktok.com/@novasmm/video/1',
    rawQuantity: 2000,
  });

  assert.equal(res.valid, true);
  assert.equal(res.effectiveQuantity, 2000);
  assert.equal(res.subtotal, 200);
  assert.equal(res.discountPercent, 5);
  assert.equal(res.discountAmount, 10);
  assert.equal(res.totalPayment, 190);
  assert.equal(res.perOrderPayment, 190);
  assert.equal(res.providerCostPerOrder, 120);
  assert.equal(res.profitPerOrder, 70);
});

test('calculateOrderPricing handles multiple links and custom comments', () => {
  const multi = calculateOrderPricing({
    service: { ...sampleService, type: 'custom_comments', minBuy: 2 },
    rank: 'member',
    rawLinks: 'https://ig.com/p/1\n\nhttps://ig.com/p/2\n',
    rawQuantity: 999,
    rawComments: 'Nice!\nLove it!\nAwesome!',
    isMultiple: true,
  });

  assert.equal(multi.valid, true);
  assert.equal(multi.links.length, 2);
  assert.equal(multi.comments.length, 3);
  assert.equal(multi.effectiveQuantity, 3);
  assert.equal(multi.totalPayment, 0.6);
  assert.equal(multi.perOrderPayment, 0.3);
});

test('calculateOrderPricing enforces minBuy and maxBuy bounds', () => {
  const tooLow = calculateOrderPricing({
    service: sampleService,
    rank: 'member',
    rawLinks: 'https://example.com',
    rawQuantity: 50,
  });
  assert.equal(tooLow.valid, false);
  assert.equal(tooLow.error, 'MIN_QUANTITY:100');

  const tooHigh = calculateOrderPricing({
    service: sampleService,
    rank: 'member',
    rawLinks: 'https://example.com',
    rawQuantity: 60000,
  });
  assert.equal(tooHigh.valid, false);
  assert.equal(tooHigh.error, 'MAX_QUANTITY:50000');
});

test('formatCurrency formats THB, USD, and VND properly', () => {
  assert.equal(formatCurrency(1500, 'THB'), '฿1,500.00');
  assert.match(formatCurrency(355, 'USD'), /^≈ \$10\.000$/);
  assert.match(formatCurrency(10, 'VND'), /^≈ 7,200 ₫$/);
});

test('validateVoucher enforces expiry, usage cap, rank restriction, and per-user uniqueness', () => {
  const baseVoucher: Voucher = {
    id: 1,
    code: 'NOVA50',
    value: 50,
    maxUses: 2,
    usedCount: 1,
    usedBy: ['otheruser'],
    allowedRanks: [],
    expiresAt: '2027-01-01T00:00:00.000Z',
    status: true,
  };

  const ok = validateVoucher(baseVoucher, 'novauser', 'member', '2026-11-23T00:00:00.000Z');
  assert.equal(ok.valid, true);
  assert.equal(ok.value, 50);

  const reused = validateVoucher(baseVoucher, 'otheruser', 'member', '2026-11-23T00:00:00.000Z');
  assert.equal(reused.valid, false);
  assert.equal(reused.error, 'VOUCHER_ALREADY_USED');

  const expired = validateVoucher(baseVoucher, 'novauser', 'member', '2028-01-01T00:00:00.000Z');
  assert.equal(expired.valid, false);
  assert.equal(expired.error, 'VOUCHER_EXPIRED');

  const vipVoucher: Voucher = { ...baseVoucher, code: 'VIP200', allowedRanks: ['gold', 'agency'] };
  const restricted = validateVoucher(vipVoucher, 'novauser', 'member', '2026-11-23T00:00:00.000Z');
  assert.equal(restricted.valid, false);
  assert.equal(restricted.error, 'VOUCHER_RANK_RESTRICTED');
});

test('parseLines trims whitespace and drops empty lines', () => {
  assert.deepEqual(parseLines('  a \n\n b \r\n  '), ['a', 'b']);
});

test('calculateOrderPricing applies 100% free waiver for admin role while charging regular users normally', () => {
  const userOrder = calculateOrderPricing({
    service: sampleService,
    rank: 'member',
    role: 'user',
    rawLinks: 'https://tiktok.com/@user/video/1',
    rawQuantity: 1000,
  });
  assert.equal(userOrder.valid, true);
  assert.equal(userOrder.discountPercent, 0);
  assert.equal(userOrder.totalPayment, 100);

  const adminOrder = calculateOrderPricing({
    service: sampleService,
    rank: 'member',
    role: 'admin',
    rawLinks: 'https://tiktok.com/@admin/video/1\nhttps://tiktok.com/@admin/video/2',
    rawQuantity: 1000,
    isMultiple: true,
  });
  assert.equal(adminOrder.valid, true);
  assert.equal(adminOrder.discountPercent, 100);
  assert.equal(adminOrder.subtotal, 200);
  assert.equal(adminOrder.discountAmount, 200);
  assert.equal(adminOrder.perOrderPayment, 0);
  assert.equal(adminOrder.totalPayment, 0);

  const adminCommentsOrder = calculateOrderPricing({
    service: { ...sampleService, type: 'custom_comments', minBuy: 3 },
    rank: 'agency',
    role: 'admin',
    rawLinks: 'https://ig.com/p/admin-free',
    rawQuantity: 3,
    rawComments: 'One\nTwo\nThree',
  });
  assert.equal(adminCommentsOrder.valid, true);
  assert.equal(adminCommentsOrder.effectiveQuantity, 3);
  assert.equal(adminCommentsOrder.discountPercent, 100);
  assert.equal(adminCommentsOrder.totalPayment, 0);
});
