import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import { readFileSync } from 'node:fs';

const { version } = JSON.parse(readFileSync(new URL('./package.json', import.meta.url)));

// `ROUTER=http://192.168.1.1 npm run dev` proxies ubus calls to a real
// device, so the UI can be developed on a PC against live data.
const router = process.env.ROUTER || 'http://192.168.1.1';

export default defineConfig({
  plugins: [svelte()],
  define: {
    __APP_VERSION__: JSON.stringify(version)
  },
  base: './',
  build: {
    outDir: 'dist',
    emptyOutDir: true
  },
  server: {
    proxy: {
      '/ubus': { target: router, changeOrigin: true }
    }
  }
});
