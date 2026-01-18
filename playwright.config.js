// @ts-check
const { defineConfig, devices } = require('@playwright/test');

// Load global app settings from environment variable (set by PHP)
let globalConfig = {};
globalConfig = JSON.parse(process.env.PLAYWRIGHT_CONFIG || '{}');


// Pull just app_url (the rest stays static)
const appUrl = globalConfig.app_url;
const storageDir = globalConfig.storage_dir;

if (!storageDir) {
    throw new Error('PLAYWRIGHT_CONFIG.storage_dir is not set. Run tests via: php cli.php playwright-test:test');
}

/**
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig({
    testDir: './tests/playwright',
    outputDir: storageDir + '/playwright/playwright-results',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: 0,
    workers: undefined, // Auto-detect based on machine capabilities (CPU cores)
    reporter: 'list',
    timeout: 30000,
    expect: {
        timeout: 5000,
    },

    use: {
        baseURL: appUrl,
        trace: 'off',
        screenshot: 'off',
        video: 'off',
        headless: true,
        actionTimeout: 15000,
        navigationTimeout: 15000,
    },

    projects: [
        {
            name: 'chromium',
            use: {
                ...devices['Desktop Chrome'],
            },
        },
    ],
});