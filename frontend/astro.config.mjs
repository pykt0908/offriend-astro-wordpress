// @ts-check
import { defineConfig } from 'astro/config';

import tailwindcss from '@tailwindcss/vite';

import react from '@astrojs/react';

import node from '@astrojs/node';

export default defineConfig({
  output: 'server',
  build: {
    inlineStylesheets: 'auto',
  },
  prefetch: {
    prefetchAll: false,
    defaultStrategy: 'hover',
  },
  vite: {
    plugins: [tailwindcss()],
    ssr: {
      external: ['sharp'],
    },
  },

  integrations: [react()],

  adapter: node({
    mode: 'standalone'
  })
});