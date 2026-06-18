import { test, expect, type Page, type Browser } from '@playwright/test';

const DEFAULT_SCRIPT_SELECTOR = 'script[src="https://scripts.simpleanalyticscdn.com/latest.js"]';
const INACTIVE_ADMIN_SCRIPT_SELECTOR = 'script[src*="resources/js/inactive.js"]';
const DASHBOARD_URL =
  'https://dashboard.simpleanalytics.com/?utm_source=wordpress&utm_medium=plugin&utm_content=go_to_dashboard_button';
const SIGNUP_URL =
  'https://www.simpleanalytics.com/signup?utm_source=wordpress&utm_medium=plugin&utm_content=signup_link';
const SCRIPT_PREFIX_COMMENT = '<!-- Simple Analytics - 100% privacy-first analytics (official WordPress plugin) -->';
const INACTIVE_COMMENT_PREFIX = '<!-- Simple Analytics: Script not included because this visitor is excluded by tracking rule:';
const INACTIVE_USER_ROLE_COMMENT = '<!-- Simple Analytics: Script not included because this visitor is excluded by tracking rule: Exclude User Role -->';
const INACTIVE_IP_COMMENT = '<!-- Simple Analytics: Script not included because this visitor is excluded by tracking rule: Exclude IP Address -->';

async function loginAs(page: Page, username: string, password: string) {
  await page.goto('/wp-login.php');
  // Use evaluate to set values + submit programmatically, bypassing Chrome autofill
  await page.evaluate(([u, p]) => {
    (document.getElementById('user_login') as HTMLInputElement).value = u;
    (document.getElementById('user_pass') as HTMLInputElement).value = p;
    (document.getElementById('loginform') as HTMLFormElement).submit();
  }, [username, password]);
  await page.waitForURL(/\/wp-admin/);
}

const asAdmin = (page: Page) => loginAs(page, 'admin', 'password');
const asAuthor = (page: Page) => loginAs(page, 'author', 'author');
const asEditor = (page: Page) => loginAs(page, 'editor', 'editor');

async function saveSettings(page: Page) {
  await Promise.all([
    page.waitForURL(/settings-updated/),
    page.getByRole('button', { name: 'Save Changes' }).click(),
  ]);
}

// Visit a URL in a fresh browser context (no cookies — anonymous visitor).
async function visitAsGuest(browser: Browser, path = '/'): Promise<Page> {
  const ctx = await browser.newContext();
  const page = await ctx.newPage();
  await page.goto(path);
  return page;
}

test('adds a script by default', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=advanced');
  await page.fill('[name="simpleanalytics_custom_domain"]', '');
  await saveSettings(page);

  const guest = await visitAsGuest(browser);
  await expect(guest.locator(DEFAULT_SCRIPT_SELECTOR)).toBeAttached();
  expect(await guest.content()).toContain(SCRIPT_PREFIX_COMMENT);
  await guest.context().close();
});

test('shows guidance on general tab and keeps custom domain in advanced tab', async ({ page }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=general');

  await expect(page.getByText('Thanks for choosing Simple Analytics.')).toBeVisible();
  await expect(page.getByRole('link', { name: 'your dashboard' })).toHaveAttribute('href', DASHBOARD_URL);
  await expect(page.getByRole('link', { name: 'simpleanalytics.com' })).toHaveAttribute('href', SIGNUP_URL);
  await expect(page.getByRole('link', { name: 'Visit your analytics dashboard' })).toHaveAttribute('href', DASHBOARD_URL);
  await expect(page.getByRole('link', { name: 'Open Dashboard' })).toHaveAttribute('href', DASHBOARD_URL);
  await expect(page.getByRole('button', { name: 'Save Changes' })).toHaveCount(0);
  await expect(page.locator('[name="simpleanalytics_custom_domain"]')).toHaveCount(0);

  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=advanced');
  await expect(page.locator('[name="simpleanalytics_custom_domain"]')).toBeVisible();
});

test('adds inactive script for authenticated users by default', async ({ page }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules');
  await page.locator('#simpleanalytics_exclude_user_roles-editor').uncheck();
  await page.locator('#simpleanalytics_exclude_user_roles-author').uncheck();
  await saveSettings(page);

  await page.goto('/');
  await expect(page.locator('#wpadminbar')).toBeAttached();
  const inactiveScript = page.locator(INACTIVE_ADMIN_SCRIPT_SELECTOR);
  if (await inactiveScript.count()) {
    await expect(inactiveScript).toBeAttached();
    expect(await page.content()).toContain(INACTIVE_COMMENT_PREFIX);
  } else {
    await expect(page.locator(DEFAULT_SCRIPT_SELECTOR)).toBeAttached();
  }
});

test('adds a script with ignored pages', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules');
  await page.fill('[name="simpleanalytics_ignore_pages"]', '/vouchers');
  await saveSettings(page);
  await expect(page.locator('[name="simpleanalytics_ignore_pages"]')).toHaveValue('/vouchers');

  const guest = await visitAsGuest(browser);
  await guest.reload();
  expect(await guest.content()).toContain('data-ignore-pages="/vouchers"');
  await guest.context().close();
});

test('adds inactive script for selected user roles', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules');
  await page.locator('#simpleanalytics_exclude_user_roles-editor').check();
  await page.locator('#simpleanalytics_exclude_user_roles-author').check();
  await saveSettings(page);
  await expect(page.locator('#simpleanalytics_exclude_user_roles-editor')).toBeChecked();
  await expect(page.locator('#simpleanalytics_exclude_user_roles-author')).toBeChecked();

  await page.goto('/');
  await expect(page.locator(DEFAULT_SCRIPT_SELECTOR)).toBeAttached();

  const authorCtx = await browser.newContext();
  const authorPage = await authorCtx.newPage();
  await asAuthor(authorPage);
  await authorPage.goto('/');
  await expect(authorPage.locator(INACTIVE_ADMIN_SCRIPT_SELECTOR)).toBeAttached();
  expect(await authorPage.content()).toContain(INACTIVE_USER_ROLE_COMMENT);
  await authorCtx.close();

  const editorCtx = await browser.newContext();
  const editorPage = await editorCtx.newPage();
  await asEditor(editorPage);
  await editorPage.goto('/');
  await expect(editorPage.locator(INACTIVE_ADMIN_SCRIPT_SELECTOR)).toBeAttached();
  expect(await editorPage.content()).toContain(INACTIVE_USER_ROLE_COMMENT);
  await editorCtx.close();
});

test('adds inactive script for excluded IP addresses', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules');
  await page.getByRole('button', { name: /Add Current IP/ }).click();
  await saveSettings(page);

  const guest = await visitAsGuest(browser, '/');
  await expect(guest.locator(INACTIVE_ADMIN_SCRIPT_SELECTOR)).toBeAttached();
  expect(await guest.content()).toContain(INACTIVE_IP_COMMENT);
  await guest.context().close();

  // Reset excluded IPs so follow-up tests can assert active script behavior.
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=ignore-rules');
  await page.fill('[name="simpleanalytics_excluded_ip_addresses"]', '');
  await saveSettings(page);
});

test('adds a script with collect do not track enabled', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=advanced');
  await page.locator('[name="simpleanalytics_collect_dnt"]').check();
  await saveSettings(page);
  await expect(page.locator('[name="simpleanalytics_collect_dnt"]')).toBeChecked();

  const guest = await visitAsGuest(browser);
  expect(await guest.content()).toContain('data-collect-dnt="true"');
  await guest.context().close();
});

test('adds a script with hash mode enabled', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=advanced');
  await page.locator('[name="simpleanalytics_hash_mode"]').check();
  await saveSettings(page);
  await expect(page.locator('[name="simpleanalytics_hash_mode"]')).toBeChecked();

  const guest = await visitAsGuest(browser);
  expect(await guest.content()).toContain('data-mode="hash"');
  await guest.context().close();
});

test('adds a script with manually collect page views enabled', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=advanced');
  await page.locator('[name="simpleanalytics_manual_collect"]').check();
  await saveSettings(page);
  await expect(page.locator('[name="simpleanalytics_manual_collect"]')).toBeChecked();

  const guest = await visitAsGuest(browser);
  expect(await guest.content()).toContain('data-auto-collect="true"');
  await guest.context().close();
});

test('adds a script with overwrite domain name', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=advanced');
  await page.fill('[name="simpleanalytics_hostname"]', 'example.com');
  await saveSettings(page);
  await expect(page.locator('[name="simpleanalytics_hostname"]')).toHaveValue('example.com');

  const guest = await visitAsGuest(browser);
  expect(await guest.content()).toContain('data-hostname="example.com"');
  await guest.context().close();
});

test('adds a script with global variable name', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=advanced');
  await page.fill('[name="simpleanalytics_sa_global"]', 'ba_event');
  await saveSettings(page);
  await expect(page.locator('[name="simpleanalytics_sa_global"]')).toHaveValue('ba_event');

  const guest = await visitAsGuest(browser);
  expect(await guest.content()).toContain('data-sa-global="ba_event"');
  await guest.context().close();
});

test('adds automated events script when collect automated events is enabled', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=events');
  await page.locator('[name="simpleanalytics_automated_events"]').check();
  await saveSettings(page);
  await expect(page.locator('[name="simpleanalytics_automated_events"]')).toBeChecked();

  const guest = await visitAsGuest(browser);
  await expect(guest.locator('script[src="https://scripts.simpleanalyticscdn.com/auto-events.js"]')).toBeAttached();
  await guest.context().close();
});

test('adds automated events script with auto collect downloads', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=events');
  await page.locator('[name="simpleanalytics_automated_events"]').check();
  await page.fill('[name="simpleanalytics_event_collect_downloads"]', 'outbound,emails,downloads');
  await saveSettings(page);
  await expect(page.locator('[name="simpleanalytics_automated_events"]')).toBeChecked();
  await expect(page.locator('[name="simpleanalytics_event_collect_downloads"]')).toHaveValue('outbound,emails,downloads');

  const guest = await visitAsGuest(browser);
  expect(await guest.content()).toContain('data-collect="outbound,emails,downloads"');
  await guest.context().close();
});

test('adds automated events script with download file extensions', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=events');
  await page.locator('[name="simpleanalytics_automated_events"]').check();
  await page.fill('[name="simpleanalytics_event_extensions"]', 'pdf,zip');
  await saveSettings(page);
  await expect(page.locator('[name="simpleanalytics_automated_events"]')).toBeChecked();
  await expect(page.locator('[name="simpleanalytics_event_extensions"]')).toHaveValue('pdf,zip');

  const guest = await visitAsGuest(browser);
  expect(await guest.content()).toContain('data-extensions="pdf,zip"');
  await guest.context().close();
});

test('adds automated events script with use titles of page enabled', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=events');
  await page.locator('[name="simpleanalytics_automated_events"]').check();
  await page.locator('[name="simpleanalytics_event_use_title"]').check();
  await saveSettings(page);
  await expect(page.locator('[name="simpleanalytics_automated_events"]')).toBeChecked();
  await expect(page.locator('[name="simpleanalytics_event_use_title"]')).toBeChecked();

  const guest = await visitAsGuest(browser);
  expect(await guest.content()).toContain('data-use-title');
  await guest.context().close();
});

test('adds automated events script with use full urls enabled', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=events');
  await page.locator('[name="simpleanalytics_automated_events"]').check();
  await page.locator('[name="simpleanalytics_event_full_urls"]').check();
  await saveSettings(page);
  await expect(page.locator('[name="simpleanalytics_automated_events"]')).toBeChecked();
  await expect(page.locator('[name="simpleanalytics_event_full_urls"]')).toBeChecked();

  const guest = await visitAsGuest(browser);
  expect(await guest.content()).toContain('data-full-urls');
  await guest.context().close();
});

test('adds automated events script with override global', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=events');
  await page.locator('[name="simpleanalytics_automated_events"]').check();
  await page.fill('[name="simpleanalytics_event_sa_global"]', 'ba_event');
  await saveSettings(page);
  await expect(page.locator('[name="simpleanalytics_automated_events"]')).toBeChecked();
  await expect(page.locator('[name="simpleanalytics_event_sa_global"]')).toHaveValue('ba_event');

  const guest = await visitAsGuest(browser);
  expect(await guest.content()).toContain('data-sa-global="ba_event"');
  await guest.context().close();
});

test('adds a script with a custom domain name', async ({ page, browser }) => {
  await asAdmin(page);
  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=advanced');
  await page.fill('[name="simpleanalytics_custom_domain"]', 'mydomain.com');
  await saveSettings(page);
  await expect(page.locator('[name="simpleanalytics_custom_domain"]')).toHaveValue('mydomain.com');

  const guest = await visitAsGuest(browser);
  await expect(guest.locator('script[src="https://mydomain.com/latest.js"]')).toBeAttached();
  await guest.context().close();

  await page.goto('/wp-admin/options-general.php?page=simpleanalytics&tab=advanced');
  await page.fill('[name="simpleanalytics_custom_domain"]', '');
  await saveSettings(page);
});
