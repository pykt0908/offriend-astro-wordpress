import sharp from 'sharp';
import { readdir, unlink, writeFile, readFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const imagesRoot = path.join(root, 'public/images');
const manifestPath = path.join(root, 'src/data/image-assets.json');

async function walk(dir) {
  const entries = await readdir(dir, { withFileTypes: true });
  const files = [];
  for (const entry of entries) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) files.push(...(await walk(full)));
    else if (/\.(jpe?g|png)$/i.test(entry.name)) files.push(full);
  }
  return files;
}

function publicUrl(dir, filename) {
  const relDir = path.relative(imagesRoot, dir).split(path.sep).filter(Boolean).join('/');
  return `/images${relDir ? `/${relDir}` : ''}/${filename}`;
}

async function writeWebp(input, output, resize, quality = 74) {
  let pipeline = sharp(input).rotate();
  if (resize) pipeline = pipeline.resize(resize);
  await pipeline.webp({ quality, effort: 5, alphaQuality: 100 }).toFile(output);
  return sharp(output).metadata();
}

const manifest = JSON.parse(await readFile(manifestPath, 'utf8').catch(() => '{}'));
const sources = await walk(imagesRoot);
if (sources.length === 0) {
  console.log('No JPEG or PNG files left to convert.');
  process.exit(0);
}

for (const file of sources) {
  const dir = path.dirname(file);
  const ext = path.extname(file);
  const base = path.basename(file, ext);
  const folder = path.relative(imagesRoot, dir).split(path.sep)[0] || '';
  const meta = await sharp(file).metadata();
  const srcW = meta.width || 1;

  if (folder === 'solutions' || folder === '') {
    const cap = Math.min(srcW, 1600);
    const widths = [640, 960, 1280, 1600].filter((width) => width < cap);
    const main = await writeWebp(file, path.join(dir, `${base}.webp`), {
      width: cap,
      withoutEnlargement: true,
    });
    const variants = [];
    for (const width of widths) {
      const variantMeta = await writeWebp(file, path.join(dir, `${base}-${width}.webp`), { width });
      variants.push(`${publicUrl(dir, `${base}-${width}.webp`)} ${variantMeta.width}w`);
    }
    variants.push(`${publicUrl(dir, `${base}.webp`)} ${main.width}w`);
    manifest[publicUrl(dir, `${base}.webp`)] = {
      width: main.width,
      height: main.height,
      srcset: variants.join(', '),
      sizes: folder === 'solutions' ? '(min-width: 1024px) 300px, (min-width: 768px) 45vw, 100vw' : '100vw',
    };
  } else if (folder === 'tools') {
    const main = await writeWebp(file, path.join(dir, `${base}.webp`), {
      width: Math.min(srcW, 800),
      withoutEnlargement: true,
    });
    manifest[publicUrl(dir, `${base}.webp`)] = { width: main.width, height: main.height };
  } else if (folder === 'logos') {
    const main = await writeWebp(file, path.join(dir, `${base}.webp`), {
      height: Math.min(meta.height || 168, 168),
      withoutEnlargement: true,
    });
    manifest[publicUrl(dir, `${base}.webp`)] = { width: main.width, height: main.height };
  } else if (folder === 'team') {
    const main = await writeWebp(file, path.join(dir, `${base}.webp`), {
      width: Math.min(srcW, 512),
      withoutEnlargement: true,
    }, 85);
    manifest[publicUrl(dir, `${base}.webp`)] = { width: main.width, height: main.height };
  }

  await unlink(file);
}

await writeFile(manifestPath, `${JSON.stringify(manifest, null, 2)}\n`);
console.log(`Wrote ${Object.keys(manifest).length} images to ${path.relative(root, manifestPath)}`);
