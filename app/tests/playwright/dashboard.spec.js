// @ts-check
const { test, expect } = require('@playwright/test');
const path = require('path');
const fs = require('fs');

const config = JSON.parse(process.env.PLAYWRIGHT_CONFIG || '{}');
const screenshotDir = path.join(config.storage_dir, 'playwright', 'screenshots');

// Ensure screenshot dir exists
if (!fs.existsSync(screenshotDir)) {
    fs.mkdirSync(screenshotDir, { recursive: true });
}

test.describe('dashboard', () => {
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

    test('dashboard profile page loads', async ({ page }, testInfo) => {
        await page.goto(url + '/dashboard');

        await expect(page.locator('.account-sidebar')).toBeVisible();
        await expect(page.locator('.account-sidebar__link--active')).toContainText('Profile');
        await expect(page.locator('#name')).toBeVisible();
        await expect(page.locator('#email')).toBeVisible();

        // Visual screenshot
        const viewport = page.viewportSize();
        const screenshotPath = path.join(screenshotDir, `dashboard-profile-${viewport.width}px.png`);
        await page.screenshot({ path: screenshotPath, fullPage: true });
        console.log('Screenshot saved:', screenshotPath);
    });

    test('dashboard security page loads', async ({ page }, testInfo) => {
        await page.goto(url + '/dashboard/security');

        await expect(page.locator('.account-sidebar')).toBeVisible();
        await expect(page.locator('.account-sidebar__link--active')).toContainText('Security');
        await expect(page.locator('#current_password')).toBeVisible();
        await expect(page.locator('#new_password')).toBeVisible();

        // Visual screenshot
        const viewport = page.viewportSize();
        const screenshotPath = path.join(screenshotDir, `dashboard-security-${viewport.width}px.png`);
        await page.screenshot({ path: screenshotPath, fullPage: true });
        console.log('Screenshot saved:', screenshotPath);
    });

    test('can navigate between profile and security', async ({ page }) => {
        await page.goto(url + '/dashboard');

        // Click security link
        await page.click('a[href*="/dashboard/security"]');
        await page.waitForURL('**/dashboard/security', { timeout: 5000 });
        await expect(page.locator('#current_password')).toBeVisible();

        // Click profile link
        await page.click('a[href*="/dashboard"]:not([href*="/security"])');
        await page.waitForURL(url + '/dashboard', { timeout: 5000 });
        await expect(page.locator('#name')).toBeVisible();
    });

    test('can update profile name', async ({ page }) => {
        await page.goto(url + '/dashboard');

        await page.fill('#name', 'Updated Name');
        await page.click('button[type="submit"]');

        await page.waitForURL(url + '/dashboard', { timeout: 5000 });
        await expect(page.locator('.account-content__success')).toBeVisible();
    });

    test('can change password', async ({ page }) => {
        await page.goto(url + '/dashboard/security');

        await page.fill('#current_password', password);
        await page.fill('#new_password', 'NewTestPass456!');
        await page.click('button[type="submit"]');

        await page.waitForURL('**/dashboard/security', { timeout: 5000 });
        await expect(page.locator('.account-content__success')).toBeVisible();
    });

    test('wrong current password shows error', async ({ page }) => {
        await page.goto(url + '/dashboard/security');

        await page.fill('#current_password', 'wrongpassword');
        await page.fill('#new_password', 'NewTestPass456!');
        await page.click('button[type="submit"]');

        await expect(page.locator('.account-content__errors')).toBeVisible();
    });
});
