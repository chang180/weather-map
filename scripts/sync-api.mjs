import { cpSync, mkdirSync, rmSync } from 'node:fs';

const dest = 'public/api';

rmSync(dest, { recursive: true, force: true });
mkdirSync(dest, { recursive: true });
cpSync('api', dest, {
  recursive: true,
  filter: (src) => !src.replace(/\\/g, '/').includes('/cache'),
});
