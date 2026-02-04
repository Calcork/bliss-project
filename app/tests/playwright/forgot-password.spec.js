// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Fetches the password reset URL from Mailhog, decoding quoted-printable encoding.
 * @param {string} mailhog_api
 * @param {string} email
 * @returns {Promise<string>}
 */
async function extractResetUrl(mailhog_api, email) {
    const response = await fetch(mailhog_api + '/api/v2/search?kind=to&query=' + encodeURIComponent(email));
    const data = await response.json();
    expect(data.count).toBeGreaterThanOrEqual(1);

    // Decode quoted-printable: remove soft line breaks (=\r\n) and decode =XX sequences
    const raw_body = data.items[0].Content.Body;
    const decoded = raw_body
        .replace(/=\r?\n/g, '')
        .replace(/=([0-9A-Fa-f]{2})/g, (/** @type {string} */ _, /** @type {string} */ hex) => String.fromCharCode(parseInt(hex, 16)));

    const match = decoded.match(/href="([^"]*reset-password[^"]*)"/);
    expect(match).not.toBeNull();
    return match[1];
}

test.describe('forgot password', () => {
    test.describe.configure({ mode: 'serial' });
    /** @type {string} */
    let url;
    /** @type {string} */
    let email;
    /** @type {string} */
    let old_password;
    /** @type {string} */
    let new_password;
    /** @type {string} */
    let bypass_key;
    /** @type {string} */
    let mailhog_api;

    test.beforeAll(() => {
        url = process.env.TEST_URL;
        email = process.env.TEST_EMAIL;
        old_password = process.env.OLD_PASSWORD;
        new_password = process.env.NEW_PASSWORD;
        bypass_key = process.env.BYPASS_KEY;
        mailhog_api = process.env.MAILHOG_API;
    });

    test.beforeEach(async ({ page }) => {
        await page.setExtraHTTPHeaders({ 'X-Bypass-Key': bypass_key });
    });

    test('forgot password page loads', async ({ page }) => {
        await page.goto(url + '/forgot-password');
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator('button[type="submit"]')).toBeVisible();
    });

    test('login page has forgot password link', async ({ page }) => {
        await page.goto(url + '/login');
        const link = page.locator('a[href*="/forgot-password"]');
        await expect(link).toBeVisible();
    });

    test('submitting empty email shows validation', async ({ page }) => {
        await page.goto(url + '/forgot-password');

        // Submit without filling email — browser validation prevents submission
        await page.click('button[type="submit"]');

        // Should stay on same page (browser blocked it)
        await expect(page).toHaveURL(/forgot-password/);
        await expect(page.locator('#email')).toBeVisible();
    });

    test('submitting valid email redirects to login with success message', async ({ page }) => {
        await page.goto(url + '/forgot-password');
        await page.fill('#email', email);
        await page.click('button[type="submit"]');

        await page.waitForURL('**/login**', { timeout: 10000 });
        await expect(page.locator('.login-form__success')).toBeVisible();
        await expect(page.locator('.login-form__success')).toContainText('password reset link');
    });

    test('submitting nonexistent email still shows success (no enumeration)', async ({ page }) => {
        await page.goto(url + '/forgot-password');
        await page.fill('#email', 'nobody-here@test.local');
        await page.click('button[type="submit"]');

        await page.waitForURL('**/login**', { timeout: 10000 });
        await expect(page.locator('.login-form__success')).toBeVisible();
    });

    test('password reset email is sent via Mailhog', async ({ page }) => {
        // Clear mailhog
        await fetch(mailhog_api + '/api/v1/messages', { method: 'DELETE' });

        // Request reset
        await page.goto(url + '/forgot-password');
        await page.fill('#email', email);
        await page.click('button[type="submit"]');
        await page.waitForURL('**/login**', { timeout: 10000 });

        // Check Mailhog for the reset email
        const response = await fetch(mailhog_api + '/api/v2/search?kind=to&query=' + encodeURIComponent(email));
        const data = await response.json();

        expect(data.count).toBeGreaterThanOrEqual(1);

        const message = data.items[0];
        expect(message.Content.Headers.Subject[0]).toContain('Reset');
        expect(message.Content.Headers.To[0]).toContain(email);
    });

    test('full flow: forgot password, click reset link, set new password, login', async ({ page }) => {
        // Clear mailhog
        await fetch(mailhog_api + '/api/v1/messages', { method: 'DELETE' });

        // Step 1: Request password reset
        await page.goto(url + '/forgot-password');
        await page.fill('#email', email);
        await page.click('button[type="submit"]');
        await page.waitForURL('**/login**', { timeout: 10000 });

        // Step 2: Get reset link from Mailhog
        const reset_url = await extractResetUrl(mailhog_api, email);

        // Step 3: Visit the reset link
        await page.goto(reset_url);
        await expect(page.locator('#password')).toBeVisible();

        // Step 4: Set new password
        await page.fill('#password', new_password);
        await page.click('button[type="submit"]');

        // Should redirect to login with success
        await page.waitForURL('**/login**', { timeout: 10000 });
        await expect(page.locator('.login-form__success')).toBeVisible();
        await expect(page.locator('.login-form__success')).toContainText('password has been reset');

        // Step 5: Login with old password should fail
        await page.fill('#email', email);
        await page.fill('#password', old_password);
        await page.click('button[type="submit"]');
        await expect(page.locator('.login-form__errors')).toBeVisible();

        // Step 6: Login with new password should succeed
        await page.goto(url + '/login');
        await page.fill('#email', email);
        await page.fill('#password', new_password);
        await page.click('button[type="submit"]');
        await page.waitForURL(url + '/', { timeout: 10000 });
        await expect(page.locator('a[href*="/logout"]')).toBeVisible();
    });

    test('invalid reset token shows error', async ({ page }) => {
        const response = await page.goto(url + '/reset-password/r/token/invalidtoken123abc');
        expect(response.status()).toBe(400);
    });

    test('used reset token cannot be reused', async ({ page }) => {
        // Clear mailhog
        await fetch(mailhog_api + '/api/v1/messages', { method: 'DELETE' });

        // Request reset
        await page.goto(url + '/forgot-password');
        await page.fill('#email', email);
        await page.click('button[type="submit"]');
        await page.waitForURL('**/login**', { timeout: 10000 });

        // Get reset link
        const reset_url = await extractResetUrl(mailhog_api, email);

        // Use the token
        await page.goto(reset_url);
        await page.fill('#password', new_password);
        await page.click('button[type="submit"]');
        await page.waitForURL('**/login**', { timeout: 10000 });

        // Try to reuse the same token
        const reuse_response = await page.goto(reset_url);
        expect(reuse_response.status()).toBe(400);
    });
});
