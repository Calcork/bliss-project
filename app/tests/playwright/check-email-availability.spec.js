// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('email availability check', () => {
    /** @type {string} */
    let url;
    /** @type {string} */
    let taken_email;
    /** @type {string} */
    let fresh_email;
    /** @type {string} */
    let bypass_key;

    test.beforeAll(() => {
        url = process.env.TEST_URL;
        taken_email = process.env.TAKEN_EMAIL;
        fresh_email = process.env.FRESH_EMAIL;
        bypass_key = process.env.BYPASS_KEY;
    });

    test.beforeEach(async ({ page }) => {
        await page.setExtraHTTPHeaders({ 'X-Bypass-Key': bypass_key });
    });

    test('shows "available" for a fresh email', async ({ page }) => {
        await page.goto(url + '/register');
        await expect(page.locator('#email')).toBeVisible();

        await page.fill('#email', fresh_email);

        const status = page.locator('.register-form__email-status');
        await expect(status).toBeVisible({ timeout: 5000 });
        await expect(status).toHaveClass(/register-form__email-status--available/, { timeout: 5000 });
    });

    test('shows "taken" for an existing email', async ({ page }) => {
        await page.goto(url + '/register');
        await expect(page.locator('#email')).toBeVisible();

        await page.fill('#email', taken_email);

        const status = page.locator('.register-form__email-status');
        await expect(status).toBeVisible({ timeout: 5000 });
        await expect(status).toHaveClass(/register-form__email-status--taken/, { timeout: 5000 });
    });

    test('does not check availability for email without @', async ({ page }) => {
        await page.goto(url + '/register');
        await expect(page.locator('#email')).toBeVisible();

        await page.fill('#email', 'notanemail');

        // Wait a bit longer than the debounce (400ms) to confirm no status appears
        await page.waitForTimeout(800);
        await expect(page.locator('.register-form__email-status')).not.toBeVisible();
    });

    test('API returns available:true for fresh email', async ({ request }) => {
        const response = await request.get(url + '/api/check-email', {
            params: { email: fresh_email },
            headers: { 'X-Bypass-Key': bypass_key },
        });

        expect(response.status()).toBe(200);
        const body = await response.json();
        expect(body.available).toBe(true);
    });

    test('API returns available:false for taken email', async ({ request }) => {
        const response = await request.get(url + '/api/check-email', {
            params: { email: taken_email },
            headers: { 'X-Bypass-Key': bypass_key },
        });

        expect(response.status()).toBe(200);
        const body = await response.json();
        expect(body.available).toBe(false);
    });

    test('API returns 422 for invalid email', async ({ request }) => {
        const response = await request.get(url + '/api/check-email', {
            params: { email: 'not-valid' },
            headers: { 'X-Bypass-Key': bypass_key },
        });

        expect(response.status()).toBe(422);
        const body = await response.json();
        expect(body.available).toBe(false);
    });
});
