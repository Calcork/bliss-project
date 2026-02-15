// @ts-check
const { test, expect } = require('@playwright/test');
const path = require('path');
const fs = require('fs');

const config = JSON.parse(process.env.PLAYWRIGHT_CONFIG || '{}');
const screenshotDir = path.join(config.storage_dir, 'playwright', 'screenshots');

if (!fs.existsSync(screenshotDir)) {
    fs.mkdirSync(screenshotDir, { recursive: true });
}

test.describe('404 not found page', () => {
    /** @type {string} */
    let url;
    /** @type {string} */
    let bypass_key;

    test.beforeAll(() => {
        url = process.env.TEST_URL;
        bypass_key = process.env.BYPASS_KEY;
    });

    test.beforeEach(async ({ page }) => {
        await page.setExtraHTTPHeaders({ 'X-Bypass-Key': bypass_key });
    });

    test('returns 404 status for nonexistent page', async ({ page }) => {
        const response = await page.goto(url + '/this-page-does-not-exist-xyz');
        expect(response.status()).toBe(404);
    });

    test('displays 404 content', async ({ page }) => {
        await page.goto(url + '/this-page-does-not-exist-xyz');

        await expect(page.locator('.not-found-content__code')).toContainText('404');
        await expect(page.locator('.not-found-content__title')).toBeVisible();
        await expect(page.locator('.not-found-content__link')).toBeVisible();

        const viewport = page.viewportSize();
        const screenshotPath = path.join(screenshotDir, `not-found-${viewport.width}px.png`);
        await page.screenshot({ path: screenshotPath, fullPage: true });
        console.log('Screenshot saved:', screenshotPath);
    });

    test('back to home link works', async ({ page }) => {
        await page.goto(url + '/this-page-does-not-exist-xyz');

        await page.click('.not-found-content__link');
        await page.waitForURL(url + '/', { timeout: 5000 });

        const response = await page.goto(url + '/');
        expect(response.status()).toBe(200);
    });
});
