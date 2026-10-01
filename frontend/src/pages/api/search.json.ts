import type { APIRoute } from 'astro';
import { performGlobalSearch } from '../../lib/search';

export const GET: APIRoute = async ({ request }) => {
  const url = new URL(request.url);
  const q = url.searchParams.get('q') || '';
  const data = await performGlobalSearch(q);

  return new Response(JSON.stringify(data), {
    status: 200,
    headers: {
      'Content-Type': 'application/json',
    },
  });
};
