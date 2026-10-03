import { defineMiddleware } from 'astro:middleware';

export const onRequest = defineMiddleware(async (context, next) => {
  const response = await next();
  const { pathname } = context.url;

  if (pathname.startsWith('/_astro/') || pathname.startsWith('/img/')) {
    response.headers.set('Cache-Control', 'public, max-age=31536000, immutable');
  } else if (pathname.startsWith('/images/') || pathname.startsWith('/fonts/')) {
    response.headers.set('Cache-Control', 'public, max-age=2592000');
  }

  return response;
});
