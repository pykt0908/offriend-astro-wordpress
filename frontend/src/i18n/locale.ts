import type { AstroCookies } from 'astro';

export type Locale = 'th' | 'en';

export const LOCALE_COOKIE = 'offriend_lang';

export function getLocale(cookies: AstroCookies): Locale {
  return cookies.get(LOCALE_COOKIE)?.value === 'en' ? 'en' : 'th';
}

export function localeSwitchHref(lang: Locale, pathname: string): string {
  const next = pathname.startsWith('/') && !pathname.startsWith('//') ? pathname : '/';
  return `/api/locale?lang=${lang}&next=${encodeURIComponent(next)}`;
}
