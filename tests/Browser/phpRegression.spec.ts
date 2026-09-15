import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { readdirSync } from 'node:fs';
import { basename, resolve } from 'node:path';

// Each script uses its own temporary options table, so these checks can run
// alongside browser tests without changing their WordPress settings.
for (const file of readdirSync(resolve('tests/Regression')).filter(name => name.endsWith('.php'))) {
  test(`WordPress regression: ${file}`, () => {
    const output = execFileSync(resolve('node_modules/.bin/wp-env'), [
      'run', 'cli', 'wp', 'eval-file',
      `wp-content/plugins/${basename(process.cwd())}/tests/Regression/${file}`,
    ], { encoding: 'utf8', timeout: 45000 });
    expect(output).toContain('Regression checks passed');
  });
}
