import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/Browser',
  timeout: 30000,
  use: {
    baseURL: 'http://localhost:8888',
    headless: !process.env.HEADED,
    launchOptions: {
      args: ['--disable-features=PasswordManagerRedesign,AutofillServerCommunication'],
    },
  },
  projects: [
    {
      name: 'chromium',
      use: { browserName: 'chromium' },
    },
  ],
});
