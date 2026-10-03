import assets from '../data/image-assets.json';

export interface ImageAsset {
  width: number;
  height: number;
  srcset?: string;
  sizes?: string;
}

const catalog = assets as Record<string, ImageAsset>;

interface MediaLike {
  media_details?: {
    width?: number;
    height?: number;
  };
}

interface ImageFit {
  widths?: number[];
  sizes?: string;
}

const UPLOADS_PREFIX = '/wp-content/uploads/';
const ALLOWED_WIDTHS = [320, 480, 640, 800, 960, 1280, 1600];

function uploadsPath(src: string): string | null {
  try {
    const url = new URL(src);
    if (url.protocol !== 'https:' || url.hostname !== 'api.offriend.net') return null;
    if (!url.pathname.startsWith(UPLOADS_PREFIX)) return null;
    return url.pathname.slice(UPLOADS_PREFIX.length);
  } catch {
    return null;
  }
}

export function proxiedImage(src: string, width: number): string {
  const file = uploadsPath(src);
  if (!file || !ALLOWED_WIDTHS.includes(width)) return src;
  return `/img/${width}/${file}`;
}

export function imageProps(
  src: string,
  media?: MediaLike | null,
  fallback: { width: number; height: number } = { width: 1280, height: 720 },
  fit?: ImageFit,
): { src: string; width: number; height: number; srcset?: string; sizes?: string } {
  const remote = uploadsPath(src);
  if (remote) {
    const widths = (fit?.widths || [480, 800, 1200]).filter((width) => ALLOWED_WIDTHS.includes(width));
    const chosen = widths.find((width) => width >= 800) || widths[0];
    const sourceWidth = media?.media_details?.width || fallback.width;
    const sourceHeight = media?.media_details?.height || fallback.height;
    const aspect = sourceWidth > 0 ? sourceHeight / sourceWidth : fallback.height / fallback.width;
    return {
      src: `/img/${chosen}/${remote}`,
      width: chosen,
      height: Math.max(1, Math.round(chosen * aspect)),
      srcset: widths.map((width) => `/img/${width}/${remote} ${width}w`).join(', '),
      sizes: fit?.sizes || '(min-width: 1024px) 400px, 100vw',
    };
  }

  const local = catalog[src];
  const width = media?.media_details?.width || local?.width || fallback.width;
  const height = media?.media_details?.height || local?.height || fallback.height;
  return {
    src,
    width,
    height,
    ...(local?.srcset ? { srcset: local.srcset, sizes: fit?.sizes || local.sizes || '100vw' } : {}),
  };
}
