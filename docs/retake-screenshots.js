const { chromium } = require('playwright');
const path = require('path');

const BASE_URL = 'http://localhost:8881';
const OUT_DIR  = path.join(__dirname, 'screenshots');
const CRED     = { log: 'admin', pwd: 'TempPass2026!' };

// ── Slugs matching the seed.php content structure ─────────────────────────
const SLUGS = {
  login:       '/login/',
  homepage:    '/homepage/',
  channel:     '/brand-identity/visual-language/',         // L2 with 4 child cards
  content:     '/brand-identity/visual-language/logo-guidelines/', // L3 with sidebar + anchor bar
  search:      '/?s=logo',
};
// ──────────────────────────────────────────────────────────────────────────

async function authenticate(browser, viewport = { width: 1440, height: 900 }) {
  const ctx  = await browser.newContext({ viewport });
  const page = await ctx.newPage();
  await page.goto(`${BASE_URL}/wp-login.php`, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.fill('#user_login', CRED.log);
  await page.fill('#user_pass',  CRED.pwd);
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 60000 }),
    page.click('#wp-submit'),
  ]);
  return { ctx, page };
}

async function frontendCtx(browser, viewport = { width: 1440, height: 900 }) {
  // Authenticate via WP login, then navigate to frontend
  const { ctx, page } = await authenticate(browser, viewport);
  // Navigate away from /wp-admin so we have a frontend-authenticated context
  await page.goto(`${BASE_URL}${SLUGS.homepage}`, { waitUntil: 'networkidle', timeout: 60000 });
  return { ctx, page };
}

(async () => {
  const browser = await chromium.launch({ headless: true });

  // ── 1. LOGIN PAGE ─────────────────────────────────────────────────────────
  console.log('\n── LOGIN ─────────────────────────────────────');
  {
    const ctx  = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await ctx.newPage();
    await page.goto(`${BASE_URL}${SLUGS.login}`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1000);
    await page.screenshot({ path: `${OUT_DIR}/login.png`, fullPage: false });
    console.log('✓ login.png');
    await ctx.close();
  }

  // ── 2. HOMEPAGE ───────────────────────────────────────────────────────────
  console.log('\n── HOMEPAGE ──────────────────────────────────');
  {
    const { ctx, page } = await frontendCtx(browser);
    await page.goto(`${BASE_URL}${SLUGS.homepage}`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1500);
    await page.screenshot({ path: `${OUT_DIR}/homepage.png`, fullPage: false });
    console.log('✓ homepage.png');
    await ctx.close();
  }

  // ── 3. CHANNEL PAGE (child card grid) ─────────────────────────────────────
  console.log('\n── CHANNEL PAGE ──────────────────────────────');
  {
    const { ctx, page } = await frontendCtx(browser);
    await page.goto(`${BASE_URL}${SLUGS.channel}`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1500);
    await page.screenshot({ path: `${OUT_DIR}/channel-page.png`, fullPage: false });
    console.log('✓ channel-page.png');
    await ctx.close();
  }

  // ── 4. CONTENT PAGE (blocks + sidebar + anchor bar) ───────────────────────
  console.log('\n── CONTENT PAGE ──────────────────────────────');
  {
    const { ctx, page } = await frontendCtx(browser);
    await page.goto(`${BASE_URL}${SLUGS.content}`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1500);
    await page.screenshot({ path: `${OUT_DIR}/content-page.png`, fullPage: false });
    console.log('✓ content-page.png');
    await ctx.close();
  }

  // ── 5. SEARCH ─────────────────────────────────────────────────────────────
  console.log('\n── SEARCH ────────────────────────────────────');
  {
    const { ctx, page } = await frontendCtx(browser);
    await page.goto(`${BASE_URL}${SLUGS.search}`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1500);
    await page.screenshot({ path: `${OUT_DIR}/search.png`, fullPage: false });
    console.log('✓ search.png');
    await ctx.close();
  }

  // ── 6. MEGA-MENU ──────────────────────────────────────────────────────────
  console.log('\n── MEGA-MENU ─────────────────────────────────');
  {
    const { ctx, page } = await frontendCtx(browser);
    await page.goto(`${BASE_URL}${SLUGS.homepage}`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1000);
    // Hover first L1 nav item to open mega-menu
    const firstNavItem = await page.$('.nav-primary > ul > li:first-child > a, .menu-primary > li:first-child > a, nav ul > li:first-child > a');
    if (firstNavItem) {
      await firstNavItem.hover();
      await page.waitForTimeout(600);
    }
    await page.screenshot({ path: `${OUT_DIR}/mega-menu.png`, fullPage: false });
    console.log('✓ mega-menu.png');
    await ctx.close();
  }

  // ── 7. WP ADMIN (ACF block editor) ───────────────────────────────────────
  console.log('\n── WP ADMIN ─────────────────────────────────');
  {
    const { ctx, page } = await authenticate(browser);
    if (!page.url().includes('/wp-admin')) {
      await page.goto(`${BASE_URL}/wp-admin/`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    }
    // Find a content page (not login/auth pages) to show ACF block editor
    await page.goto(`${BASE_URL}/wp-admin/edit.php?post_type=page`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(1500);
    const skipWords = ['login', 'homepage', 'invite', 'password', 'expired', '404', 'contact', 'footer'];
    const editLinks = await page.$$eval(
      '.wp-list-table tbody tr td.title a.row-title',
      els => els.map(a => ({ text: a.textContent.trim(), href: a.href })),
    ).catch(() => []);
    const target = editLinks.find(e => !skipWords.some(w => e.text.toLowerCase().includes(w))) || editLinks[0];
    if (target) {
      await page.goto(target.href, { waitUntil: 'domcontentloaded', timeout: 60000 });
      await page.waitForTimeout(3000);
    }
    await page.screenshot({ path: `${OUT_DIR}/wp-admin.png`, fullPage: false });
    console.log('✓ wp-admin.png —', target ? target.text : '(pages list)');
    await ctx.close();
  }

  // ── 8. MOBILE ─────────────────────────────────────────────────────────────
  console.log('\n── MOBILE ────────────────────────────────────');
  {
    const { ctx, page } = await frontendCtx(browser, { width: 390, height: 844 });
    await page.goto(`${BASE_URL}${SLUGS.homepage}`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1000);
    // Open mobile menu
    const hamburger = await page.$('[class*="hamburger"], [class*="mobile-toggle"], [class*="menu-toggle"], button[aria-label*="menu" i]');
    if (hamburger) {
      await hamburger.click();
      await page.waitForTimeout(600);
    }
    await page.screenshot({ path: `${OUT_DIR}/mobile.png`, fullPage: false });
    console.log('✓ mobile.png');
    await ctx.close();
  }

  await browser.close();
  console.log('\n✅ All screenshots done. Check docs/screenshots/');
})().catch(e => { console.error(e.message); process.exit(1); });
