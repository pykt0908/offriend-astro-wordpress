process.env.ASTRO_NODE_AUTOSTART = 'disabled';

const { handler } = await import('./dist/server/entry.mjs');
const { createServer } = await import('node:http');

const port = Number(process.env.PORT || 8080);
const host = process.env.HOST || '0.0.0.0';

function cacheControl(url) {
  const path = url.split('?')[0];
  if (path.startsWith('/_astro/') || path.startsWith('/img/')) return 'public, max-age=31536000, immutable';
  if (path.startsWith('/images/') || path.startsWith('/fonts/')) return 'public, max-age=2592000';
  return null;
}

const server = createServer((req, res) => {
  const header = cacheControl(req.url || '');
  if (header) {
    const setHeader = res.setHeader.bind(res);
    res.setHeader = (name, value) => {
      if (String(name).toLowerCase() === 'cache-control' && res.statusCode < 400) {
        return setHeader(name, header);
      }
      return setHeader(name, value);
    };
  }
  return handler(req, res);
});

server.listen(port, host, () => {
  console.log(`Offriend listening on http://${host}:${port}`);
});
