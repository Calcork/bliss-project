// @ts-check
const { test, expect } = require('@playwright/test');
const path = require('path');
const fs = require('fs');

const config = JSON.parse(process.env.PLAYWRIGHT_CONFIG || '{}');
const screenshotDir = path.join(config.storage_dir, 'playwright', 'screenshots');

if (!fs.existsSync(screenshotDir)) {
    fs.mkdirSync(screenshotDir, { recursive: true });
}

test.describe('dashboard delete account', () => {
    /** @type {string} */
    let url;
    /** @type {string} */
    let email;
    /** @type {string} */
    let password;
    /** @type {string} */
    let bypass_key;

    test.beforeAll(() => {
        url = process.env.TEST_URL;
        email = process.env.EMAIL;
        password = process.env.PASSWORD;
        bypass_key = process.env.BYPASS_KEY;
    });

    test.beforeEach(async ({ page }) => {
        await page.setExtraHTTPHeaders({ 'X-Bypass-Key': bypass_key });

        // Login
        await page.goto(url + '/login');
        await page.fill('#email', email);
        await page.fill('#password', password);
        await page.click('button[type="submit"]');
        await page.waitForURL(url + '/', { timeout: 10000 });
    });

    test('delete account page loads', async ({ page }) => {
        await page.goto(url + '/dashboard/delete-account');

        await expect(page.locator('.account-sidebar')).toBeVisible();
        await expect(page.locator('.account-card--danger')).toBeVisible();
        await expect(page.locator('#password')).toBeVisible();
        await expect(page.locator('.account-form__button--danger')).toBeVisible();

        const viewport = page.viewportSize();
        const screenshotPath = path.join(screenshotDir, `dashboard-delete-account-${viewport.width}px.png`);
        await page.screenshot({ path: screenshotPath, fullPage: true });
        console.log('Screenshot saved:', screenshotPath);
    });

    test('sidebar shows delete account link', async ({ page }) => {
        await page.goto(url + '/dashboard/delete-account');

        await expect(page.locator('.account-sidebar__link--danger')).toBeVisible();
    });

    test('wrong password shows error', async ({ page }) => {
        await page.goto(url + '/dashboard/delete-account');

        await page.fill('#password', 'wrongpassword1');
        await page.click('button[type="submit"]');

        await expect(page.locator('.account-content__errors')).toBeVisible();
    });

    test('can navigate to delete account from dashboard', async ({ page }) => {
        await page.goto(url + '/dashboard');

        await page.click('.account-sidebar__link--danger');
        await page.waitForURL('**/dashboard/delete-account', { timeout: 5000 });
        await expect(page.locator('.account-card--danger')).toBeVisible();
    });

    test('correct password deletes account and redirects to home', async ({ page }) => {
        await page.goto(url + '/dashboard/delete-account');

        await page.fill('#password', password);
        await page.click('button[type="submit"]');

        await page.waitForURL(url + '/', { timeout: 10000 });

        // Verify user is logged out - trying to access dashboard should redirect to login
        await page.goto(url + '/dashboard');
        await page.waitForURL('**/login', { timeout: 5000 });

        // Verify cannot login with deleted account
        await page.fill('#email', email);
        await page.fill('#password', password);
        await page.click('button[type="submit"]');

        await expect(page.locator('.login-form__errors')).toBeVisible();
    });
});
