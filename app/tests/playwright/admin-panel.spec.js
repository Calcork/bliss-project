// @ts-check
const { test, expect } = require('@playwright/test');
const path = require('path');
const fs = require('fs');

const config = JSON.parse(process.env.PLAYWRIGHT_CONFIG || '{}');
const screenshotDir = path.join(config.storage_dir, 'playwright', 'screenshots');

if (!fs.existsSync(screenshotDir)) {
    fs.mkdirSync(screenshotDir, { recursive: true });
}

test.describe('admin panel', () => {
    /** @type {string} */
    let url;
    /** @type {string} */
    let admin_email;
    /** @type {string} */
    let password;
    /** @type {string} */
    let bypass_key;

    test.beforeAll(() => {
        url = process.env.TEST_URL;
        admin_email = process.env.ADMIN_EMAIL;
        password = process.env.PASSWORD;
        bypass_key = process.env.BYPASS_KEY;
    });

    test.beforeEach(async ({ page }) => {
        await page.setExtraHTTPHeaders({ 'X-Bypass-Key': bypass_key });

        // Login as admin
        await page.goto(url + '/login');
        await page.fill('#email', admin_email);
        await page.fill('#password', password);
        await page.click('button[type="submit"]');
        await page.waitForURL(url + '/', { timeout: 10000 });
    });

    test('admin dashboard shows stat cards', async ({ page }) => {
        await page.goto(url + '/admin');

        await expect(page.locator('.admin-stat-card')).toHaveCount(3);
        await expect(page.locator('.admin-stat-card__value').first()).toBeVisible();
        await expect(page.locator('.admin-stat-card__label').first()).toBeVisible();

        const viewport = page.viewportSize();
        const screenshotPath = path.join(screenshotDir, `admin-dashboard-${viewport.width}px.png`);
        await page.screenshot({ path: screenshotPath, fullPage: true });
        console.log('Screenshot saved:', screenshotPath);
    });

    test('admin sidebar has all entity links', async ({ page }) => {
        await page.goto(url + '/admin');

        await expect(page.locator('.admin-sidebar__link')).toHaveCount(4);
        await expect(page.locator('.admin-sidebar__link--active')).toContainText('Dashboard');
    });

    test('admin users page loads with user table', async ({ page }) => {
        await page.goto(url + '/admin/entity/r/entity_slug/users');

        await expect(page.locator('.admin-sidebar__link--active')).toContainText('Users');
        await expect(page.locator('.admin-table')).toBeVisible();
        await expect(page.locator('.admin-table__body')).toBeVisible();

        const viewport = page.viewportSize();
        const screenshotPath = path.join(screenshotDir, `admin-users-${viewport.width}px.png`);
        await page.screenshot({ path: screenshotPath, fullPage: true });
        console.log('Screenshot saved:', screenshotPath);
    });

    test('admin users table shows test users', async ({ page }) => {
        await page.goto(url + '/admin/entity/r/entity_slug/users');

        // Should contain our test users
        const body = await page.locator('.admin-table__body').textContent();
        expect(body).toContain(admin_email);

        // Should show "Yes" for admin status and datetime values
        await expect(page.locator('.admin-table__body')).toContainText('Yes');
    });

    test('can navigate between admin dashboard and users', async ({ page }) => {
        await page.goto(url + '/admin');

        // Go to users
        await page.click('a[href*="/admin/entity/r/entity_slug/users"]');
        await page.waitForURL('**/admin/entity/r/entity_slug/users', { timeout: 5000 });
        await expect(page.locator('.admin-table')).toBeVisible();

        // Go back to dashboard
        await page.click('.admin-sidebar__link:has-text("Dashboard")');
        await page.waitForURL(url + '/admin', { timeout: 5000 });
        await expect(page.locator('.admin-stat-card').first()).toBeVisible();
    });

    test('non-admin user cannot access admin pages', async ({ page }) => {
        // Logout first
        await page.goto(url + '/logout');

        // Login as regular user
        const verified_email = process.env.VERIFIED_EMAIL;
        await page.goto(url + '/login');
        await page.fill('#email', verified_email);
        await page.fill('#password', password);
        await page.click('button[type="submit"]');
        await page.waitForURL(url + '/', { timeout: 10000 });

        // Try admin page - should redirect to home
        await page.goto(url + '/admin');
        await page.waitForURL(url + '/', { timeout: 5000 });
    });
});
