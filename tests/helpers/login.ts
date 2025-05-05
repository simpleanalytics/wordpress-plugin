import { Browser, Page, BrowserContext } from "@playwright/test";
import * as fs from "node:fs";

export async function loginToWordPress(browser: Browser, storagePath = "tests-browser-state.json") {
    // Skip login if state already exists
    if (fs.existsSync(storagePath)) return;

    const context = await browser.newContext();
    const page = await context.newPage();

    await page.goto("/wp-login.php");
    await page.fill("#user_login", "your-username");
    await page.fill("#user_pass", "your-password");
    await page.click("#wp-submit");

    await page.waitForURL("**/wp-admin/**");

    await context.storageState({ path: storagePath });
    await context.close();
}

export async function createLoggedInPage(
    browser: Browser,
    storagePath = "storage/wordpress-auth.json"
): Promise<{ context: BrowserContext; page: Page }> {
    const context = await browser.newContext({ storageState: storagePath });
    const page = await context.newPage();
    return { context, page };
}
