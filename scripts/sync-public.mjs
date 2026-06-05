import { cpSync, existsSync, mkdirSync, rmSync } from 'node:fs';

if (!existsSync('dist/index.html')) {
  console.error('dist/index.html not found. Run vite build first.');
  process.exit(1);
}

mkdirSync('public', { recursive: true });

cpSync('dist/index.html', 'public/index.html');

if (existsSync('public/assets')) {
  rmSync('public/assets', { recursive: true, force: true });
}

if (existsSync('dist/assets')) {
  cpSync('dist/assets', 'public/assets', { recursive: true });
}
