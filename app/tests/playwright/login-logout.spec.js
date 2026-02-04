// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('login and logout', () => {
    /** @type {string} */
    let url;
    /** @type {string} */
    let verified_email;
    /** @type {string} */
    let unverified_email;
    /** @type {string} */
    let password;
    /** @type {string} */
    let bypass_key;

    test.beforeAll(() => {
        url = process.env.TEST_URL;
        verified_email = process.env.VERIFIED_EMAIL;
        unverified_email = process.env.UNVERIFIED_EMAIL;
        password = process.env.PASSWORD;
        bypass_key = process.env.BYPASS_KEY;
    });

    test.beforeEach(async ({ page }) => {
        await page.setExtraHTTPHeaders({ 'X-Bypass-Key': bypass_key });
    });

    test('login with correct credentials redirects to homepage', async ({ page }) => {
        await page.goto(url + '/login');
        await expect(page.locator('#email')).toBeVisible();

        await page.fill('#email', verified_email);
        await page.fill('#password', password);
        await page.click('button[type="submit"]');

        await page.waitForURL(url + '/', { timeout: 10000 });

        // Homepage should show logout link, not login
        await expect(page.locator('a[href*="/logout"]')).toBeVisible();
        await expect(page.locator('a[href*="/login"]')).not.toBeVisible();
    });

    test('login with wrong password shows error', async ({ page }) => {
        await page.goto(url + '/login');
        await expect(page.locator('#email')).toBeVisible();

        await page.fill('#email', verified_email);
        await page.fill('#password', 'WrongPassword1!');
        await page.click('button[type="submit"]');

        await expect(page.locator('.login-form__errors')).toBeVisible();
        await expect(page.locator('.login-form__errors')).toContainText('Invalid email or password');
    });

    test('login with nonexistent email shows error', async ({ page }) => {
        await page.goto(url + '/login');
        await expect(page.locator('#email')).toBeVisible();

        await page.fill('#email', 'nobody@test.local');
        await page.fill('#password', password);
        await page.click('button[type="submit"]');

        await expect(page.locator('.login-form__errors')).toBeVisible();
        await expect(page.locator('.login-form__errors')).toContainText('Invalid email or password');
    });

    test('login with unverified email shows verification error', async ({ page }) => {
        await page.goto(url + '/login');
        await expect(page.locator('#email')).toBeVisible();

        await page.fill('#email', unverified_email);
        await page.fill('#password', password);
        await page.click('button[type="submit"]');

        await expect(page.locator('.login-form__errors')).toBeVisible();
        await expect(page.locator('.login-form__errors')).toContainText('verify your email');
    });

    test('logout clears session and redirects to login', async ({ page }) => {
        // Login first
        await page.goto(url + '/login');
        await page.fill('#email', verified_email);
        await page.fill('#password', password);
        await page.click('button[type="submit"]');
        await page.waitForURL(url + '/', { timeout: 10000 });

        // Click logout
        await page.click('a[href*="/logout"]');
        await page.waitForURL('**/login**', { timeout: 10000 });

        // Go back to homepage — should see login link, not logout
        await page.goto(url + '/');
        await expect(page.locator('a[href*="/login"]')).toBeVisible();
        await expect(page.locator('a[href*="/logout"]')).not.toBeVisible();
    });

    test('homepage shows login and register links when not logged in', async ({ page }) => {
        await page.goto(url + '/');

        await expect(page.locator('a[href*="/login"]')).toBeVisible();
        await expect(page.locator('a[href*="/register"]')).toBeVisible();
        await expect(page.locator('a[href*="/logout"]')).not.toBeVisible();
    });
});
