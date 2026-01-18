const { chromium } = require('@playwright/test');
(async () => {
  const browser = await chromium.launch({
    headless: true,
    args: ['--disable-gpu', '--disable-software-rasterizer', '--disable-dev-shm-usage']
  });
  const page = await browser.newPage();
  page.on('crash', () => console.log('CRASHED!'));
  try {
    await page.setContent('<html><body>TEST</body></html>');
    console.log('setContent worked!');
    const content = await page.content();
    console.log('Content:', content.substring(0, 100));
  } catch(e) {
    console.log('Error:', e.message);
  }
  await browser.close();
})();
