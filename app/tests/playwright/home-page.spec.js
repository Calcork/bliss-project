// @ts-check
const { test, expect } = require('@playwright/test');

test('home page loads successfully', async ({ page }) => {
    const url = process.env.TEST_URL;
    const response = await page.goto(url);

    expect(response.status()).toBe(200);
    await expect(page.locator('body')).not.toBeEmpty();
});
