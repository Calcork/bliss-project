// @ts-check
const { test, expect } = require('@playwright/test');

test('registration sends welcome email via Mailhog', async ({ page }) => {
    const url = process.env.TEST_URL;
    const email = process.env.TEST_EMAIL;
    const name = process.env.TEST_NAME;
    const password = process.env.TEST_PASSWORD;
    const bypass_key = process.env.BYPASS_KEY;
    const mailhog_api = process.env.MAILHOG_API;

    // Set bypass key header to skip rate limiting
    await page.setExtraHTTPHeaders({ 'X-Bypass-Key': bypass_key });

    // Go to registration page
    await page.goto(url + '/register');
    await expect(page.locator('#name')).toBeVisible();

    // Fill the form
    await page.fill('#name', name);
    await page.fill('#email', email);
    await page.fill('#password', password);

    // Submit
    await page.click('button[type="submit"]');

    // Should redirect to login
    await page.waitForURL('**/login**', { timeout: 10000 });

    // Check Mailhog for the welcome email
    const response = await fetch(mailhog_api + '/api/v2/search?kind=to&query=' + encodeURIComponent(email));
    const data = await response.json();

    expect(data.count).toBeGreaterThanOrEqual(1);

    const message = data.items[0];
    expect(message.Content.Headers.Subject[0]).toBe('Welcome to Bliss');
    expect(message.Content.Headers.To[0]).toContain(email);
});
