import { mockApi } from './mockApi';

const STATIC_MODE =
  typeof window !== 'undefined' &&
  (import.meta.env.VITE_STATIC_DEMO === '1' || window.location.hostname.endsWith('github.io'));

export async function apiRequest<T>(url: string, options: RequestInit = {}): Promise<T> {
  if (STATIC_MODE && url.startsWith('/api/')) {
    const mocked = await mockApi(url, options);
    return mocked.data as T;
  }

  const res = await fetch(url, {
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
      ...(options.headers ?? {}),
    },
    ...options,
  });

  const data = (await res.json().catch(() => ({}))) as T & { message?: string; error?: string };
  if (!res.ok) {
    throw new Error(data.message || data.error || `Request failed (${res.status})`);
  }
  return data;
}
