const { chromium } = require('playwright');

const BASE_URL = 'http://localhost:8881';
const CREDENTIALS = [
  { log: 'admin',             pwd: 'Admin123!' },
  { log: 'admin@localhost.com', pwd: 'Admin123!' },
];

(async () => {
  const browser = await chromium.launch({ headless: true });

  let authedPage = null;
  let ctx;

  for (const cred of CREDENTIALS) {
    ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const p = await ctx.newPage();
    await p.goto(`${BASE_URL}/wp-login.php`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    await p.fill('#user_login', cred.log);
    await p.fill('#user_pass', cred.pwd);
    await p.click('#wp-submit');
    await p.waitForLoadState('domcontentloaded');
    const url = p.url();
    console.log(`Login as "${cred.log}" → ${url}`);
    if (url.includes('wp-admin') && !url.includes('wp-login')) {
      authedPage = p;
      break;
    }
    await ctx.close();
  }

  if (!authedPage) {
    console.error('All credentials failed — taking wp-login screenshot as fallback');
    const fc = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const fp = await fc.newPage();
    await fp.goto(`${BASE_URL}/wp-login.php`, { waitUntil: 'domcontentloaded' });
    await fp.screenshot({ path: 'screenshots/wp-admin.png' });
    console.log('✓ wp-admin.png (wp-login fallback)');
    await browser.close();
    return;
  }

  // Landed in wp-admin — navigate to pages list
  await authedPage.goto(`${BASE_URL}/wp-admin/edit.php?post_type=page`, {
    waitUntil: 'domcontentloaded',
    timeout: 60000,
  });
  await authedPage.waitForTimeout(1500);
  console.log('Pages list URL:', authedPage.url());

  const editLinks = await authedPage.$$eval(
    '.wp-list-table tbody tr td.title a.row-title',
    els => els.map(a => ({ text: a.textContent.trim(), href: a.href })),
  ).catch(() => []);
  console.log('Pages:', JSON.stringify(editLinks.slice(0, 8)));

  const skipWords = ['login', 'homepage', 'invite', 'password', 'expired', '404'];
  const target = editLinks.find(e =>
    !skipWords.some(w => e.text.toLowerCase().includes(w)),
  ) || editLinks[0];

  if (!target) {
    await authedPage.screenshot({ path: 'screenshots/wp-admin.png' });
    console.log('✓ wp-admin.png (pages list, no content page found)');
    await browser.close();
    return;
  }

  console.log('Opening editor:', target.text);
  await authedPage.goto(target.href, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await authedPage.waitForTimeout(3000);
  console.log('Editor URL:', authedPage.url());
  await authedPage.screenshot({ path: 'screenshots/wp-admin.png' });
  console.log('✓ wp-admin.png');

  await browser.close();
})().catch(e => {
  console.error(e.message);
  process.exit(1);
});
