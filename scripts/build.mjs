import { cpSync, existsSync, mkdtempSync, rmSync } from 'node:fs';
import { execSync } from 'node:child_process';
import { join } from 'node:path';
import { tmpdir } from 'node:os';

const tmp = mkdtempSync(join(tmpdir(), 'weather-map-build-'));
const apiBackup = join(tmp, 'api');

if (existsSync('public/api')) {
  cpSync('public/api', apiBackup, { recursive: true });
}

if (existsSync('public/assets')) {
  rmSync('public/assets', { recursive: true, force: true });
}

try {
  execSync('vite build', { stdio: 'inherit' });
} catch {
  process.exit(1);
} finally {
  if (existsSync(apiBackup)) {
    cpSync(apiBackup, 'public/api', { recursive: true });
  }

  if (existsSync('dist/api')) {
    rmSync('dist/api', { recursive: true, force: true });
  }

  rmSync(tmp, { recursive: true, force: true });
}

execSync('node scripts/sync-public.mjs', { stdio: 'inherit' });
