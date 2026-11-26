import type { Platform } from '../types/smm';

export function PlatformBadge({ platform }: { platform?: Platform }) {
  if (!platform) return null;
  return (
    <span
      className="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-extrabold text-white shrink-0 shadow-xs"
      style={{ backgroundColor: platform.badgeColor || '#2563eb' }}
      aria-hidden="true"
    >
      {platform.code}
    </span>
  );
}
