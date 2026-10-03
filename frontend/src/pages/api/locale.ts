import type { APIRoute } from 'astro';
import { LOCALE_COOKIE, type Locale } from '../../i18n/locale';

export const prerender = false;

export const GET: APIRoute = ({ url, cookies, redirect }) => {
  const lang: Locale = url.searchParams.get('lang') === 'en' ? 'en' : 'th';
  let next = url.searchParams.get('next') || '/';
  if (!next.startsWith('/') || next.startsWith('//')) next = '/';

  cookies.set(LOCALE_COOKIE, lang, {
    path: '/',
    maxAge: 60 * 60 * 24 * 365,
    sameSite: 'lax',
    httpOnly: false,
  });

  return redirect(next, 302);
};
