// @ts-check
import { defineConfig } from 'astro/config';

import tailwindcss from '@tailwindcss/vite';

import react from '@astrojs/react';

import node from '@astrojs/node';

export default defineConfig({
  output: 'server',
  prefetch: {
    prefetchAll: true,
    defaultStrategy: 'hover',
  },
  vite: {
    plugins: [tailwindcss()]
  },

  integrations: [react()],

  adapter: node({
    mode: 'standalone'
  })
});