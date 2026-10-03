import type { APIRoute } from 'astro';
import { createHash } from 'node:crypto';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';

export const prerender = false;

const WIDTHS = new Set([320, 480, 640, 800, 960, 1280, 1600]);

export const GET: APIRoute = async ({ params }) => {
  const width = Number(params.width);
  const file = params.file || '';
  if (!WIDTHS.has(width) || !file || file.includes('..') || file.startsWith('/')) {
    return new Response(null, { status: 400 });
  }

  const remote = `https://api.offriend.net/wp-content/uploads/${file}`;
  const cacheDir = path.join(process.cwd(), '.cache', 'img');
  const cachePath = path.join(cacheDir, `${createHash('sha1').update(`${width}:${remote}`).digest('hex')}.webp`);

  let body: Buffer;
  try {
    body = await readFile(cachePath);
  } catch {
    let source: Response;
    try {
      source = await fetch(remote, { redirect: 'error' });
    } catch {
      return new Response(null, { status: 502 });
    }
    if (!source.ok) return new Response(null, { status: 404 });
    const type = source.headers.get('content-type') || '';
    if (!type.startsWith('image/')) return new Response(null, { status: 404 });
    const input = Buffer.from(await source.arrayBuffer());
    body = await sharp(input)
      .rotate()
      .resize({ width, withoutEnlargement: true })
      .webp({ quality: 72 })
      .toBuffer();
    await mkdir(cacheDir, { recursive: true });
    await writeFile(cachePath, body);
  }

  return new Response(body, {
    headers: {
      'Content-Type': 'image/webp',
      'Cache-Control': 'public, max-age=31536000, immutable',
    },
  });
};
