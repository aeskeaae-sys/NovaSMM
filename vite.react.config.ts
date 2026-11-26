import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import fs from 'fs';
import path from 'path';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [
    react(),
    tailwindcss(),
  ],
  root: path.resolve(__dirname, 'resources/react-app'),
  base: process.env.GITHUB_PAGES === '1' ? '/NovaSMM/' : '/app/',
  publicDir: false,
  build: {
    outDir: path.resolve(__dirname, 'public/app'),
    emptyOutDir: true,
    manifest: true,
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/react'),
    },
  },
});

