export const MICRO_SCALE = 1_000_000;

export function toMicro(amount: number): number {
  return Math.round((Number(amount) || 0) * MICRO_SCALE);
}
export function fromMicro(micro: number): number {
  return Math.round((Number(micro) || 0)) / MICRO_SCALE;
}
export function calculatePerThousand(quantity: number, pricePer1k: number): number {
  return fromMicro(Math.round(toMicro(pricePer1k) * (Number(quantity) || 0) / 1000));
}
export function calculatePercentOf(amount: number, percent: number): number {
  return fromMicro(Math.round(toMicro(amount) * (Number(percent) || 0) / 100));
}
export function subDecimal(a: number, b: number): number {
  return fromMicro(toMicro(a) - toMicro(b));
}
