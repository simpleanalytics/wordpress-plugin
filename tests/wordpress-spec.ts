import { test, expect } from "@playwright/test";
import { createLoggedInPage, loginToWordPress } from "./helpers/login";

test.beforeAll(async ({ browser }) => {
    await loginToWordPress(browser);
});

test("Access dashboard", async ({ browser }) => {
    const { page } = await createLoggedInPage(browser);

    await page.goto("/wp-admin/");
    await expect(page).toHaveURL(/wp-admin/);
});
