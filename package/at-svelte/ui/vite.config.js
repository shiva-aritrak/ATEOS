import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';

// `ROUTER=http://192.168.27.1 npm run dev` proxies ubus calls to a real
// device, so the UI can be developed on a PC against live data.
const router = process.env.ROUTER || 'http://192.168.27.1';

export default defineConfig({
  plugins: [svelte()],
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
