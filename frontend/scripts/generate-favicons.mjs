import sharp from 'sharp';
import fs from 'fs';
import path from 'path';

async function generateFavicons() {
  const rootDir = process.cwd();
  const publicDir = path.join(rootDir, 'public');

  // Emblem bounding box is 754x532.
  // In a 512x512 square canvas, let emblem width be 460, height be 325.
  // Center: left = (512-460)/2 = 26, top = (512-325)/2 = 93.
  const make512 = async (srcRel) => {
    const src = path.join(publicDir, srcRel);
    const resized = await sharp(src)
      .resize(460, 325, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } })
      .toBuffer();
    return sharp({
      create: { width: 512, height: 512, channels: 4, background: { r: 0, g: 0, b: 0, alpha: 0 } }
    })
    .composite([{ input: resized, top: 93, left: 26 }])
    .png()
    .toBuffer();
  };

  const buf16_512 = await make512('images/logos/no-bg/1.6TP.png');
  const buf15_512 = await make512('images/logos/no-bg/1.5TP.png');

  // Save 512px icon
  fs.writeFileSync(path.join(publicDir, 'favicon.png'), buf16_512);

  // Apple touch icon (180x180)
  await sharp(buf16_512).resize(180, 180).toFile(path.join(publicDir, 'apple-touch-icon.png'));

  // 32x32 and 16x16 PNG
  const buf32 = await sharp(buf16_512).resize(32, 32).png().toBuffer();
  fs.writeFileSync(path.join(publicDir, 'favicon-32x32.png'), buf32);

  const buf16 = await sharp(buf16_512).resize(16, 16).png().toBuffer();
  fs.writeFileSync(path.join(publicDir, 'favicon-16x16.png'), buf16);

  // Generate valid multi-size .ico (32x32 and 16x16)
  const icoHeader = Buffer.alloc(6);
  icoHeader.writeUInt16LE(0, 0); // reserved
  icoHeader.writeUInt16LE(1, 2); // icon type
  icoHeader.writeUInt16LE(2, 4); // 2 images

  const entry1 = Buffer.alloc(16);
  entry1.writeUInt8(32, 0); // width
  entry1.writeUInt8(32, 1); // height
  entry1.writeUInt8(0, 2);  // color count
  entry1.writeUInt8(0, 3);  // reserved
  entry1.writeUInt16LE(1, 4); // planes
  entry1.writeUInt16LE(32, 6); // bpp
  entry1.writeUInt32LE(buf32.length, 8); // size
  entry1.writeUInt32LE(6 + 16 * 2, 12); // offset: 38

  const entry2 = Buffer.alloc(16);
  entry2.writeUInt8(16, 0); // width
  entry2.writeUInt8(16, 1); // height
  entry2.writeUInt8(0, 2);  // color count
  entry2.writeUInt8(0, 3);  // reserved
  entry2.writeUInt16LE(1, 4); // planes
  entry2.writeUInt16LE(32, 6); // bpp
  entry2.writeUInt32LE(buf16.length, 8); // size
  entry2.writeUInt32LE(6 + 16 * 2 + buf32.length, 12); // offset

  const icoBuf = Buffer.concat([icoHeader, entry1, entry2, buf32, buf16]);
  fs.writeFileSync(path.join(publicDir, 'favicon.ico'), icoBuf);

  // Generate dynamic SVG favicon (Light mode uses 1.6 dark navy/cyan, Dark mode uses 1.5 light cyan)
  const base64_16 = buf16_512.toString('base64');
  const base64_15 = buf15_512.toString('base64');

  const svgContent = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  <style>
    .fav-light { display: block; }
    .fav-dark { display: none; }
    @media (prefers-color-scheme: dark) {
      .fav-light { display: none; }
      .fav-dark { display: block; }
    }
  </style>
  <image class="fav-light" href="data:image/png;base64,${base64_16}" width="512" height="512" />
  <image class="fav-dark" href="data:image/png;base64,${base64_15}" width="512" height="512" />
</svg>
`;
  fs.writeFileSync(path.join(publicDir, 'favicon.svg'), svgContent);

  // Clean test files if they exist
  ['test-favicon-1.6.png', 'test-favicon-1.5.png', 'preview-1.5-light.png', 'preview-1.6-light.png', 'preview-1.5-dark.png', 'preview-1.6-dark.png'].forEach(f => {
    const p = path.join(publicDir, f);
    if (fs.existsSync(p)) fs.unlinkSync(p);
  });

  console.log('Successfully generated all favicons!');
}

generateFavicons();
