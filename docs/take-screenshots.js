const { chromium } = require('playwright');
const path = require('path');

const BASE_URL    = 'http://localhost:8881';
const OUT_DIR     = path.join(__dirname, 'screenshots');
const COOKIE_HASH = '93ba08ff9f1dfbb606ccb546f9d0787c'; // md5('http://localhost:8881')

// Values as provided by browser DevTools (URL-encoded; decoded below)
const RAW_COOKIE_1 = 'admin%7C1783604872%7CjOdxBWFXUx4KnYqKG5D1HzpxxkmQ8qvDWbhV4ov5npE%7C6f6f13dd78d9eaab9b72d13c3dd6caae723886a754ec97b9450af08f7e05e6bc';
const RAW_COOKIE_2 = 'admin%7C1783603996%7CpK2SJE2ZyfWn77DCnwDfwwu9opv4ZW6kmtbTYgS5W4Z%7Cdf92e7d8553860bc02d1ded3b9a10c393221b9ce03e90b7715760cd3f8ae77c7';

// wordpress_logged_in_* = general session (all pages)
// wordpress_*           = auth cookie (wp-admin)
const COOKIES = [
  { name: `wordpress_logged_in_${COOKIE_HASH}`, value: decodeURIComponent(RAW_COOKIE_1) },
  { name: `wordpress_${COOKIE_HASH}`,           value: decodeURIComponent(RAW_COOKIE_2) },
];

// Known page URLs derived from the running localhost site
const CHANNEL_URL = `${BASE_URL}/brand-principles/`;
const CONTENT_URL = `${BASE_URL}/brand-principles/2nd-level-entry-1/3rd-level-entry-1/`;
const SEARCH_URL  = `${BASE_URL}/?s=brand`;

function cookieDefs(domain) {
  return COOKIES.map(c => ({ ...c, domain, path: '/' }));
}

async function run() {
  const browser = await chromium.launch({ headless: true });

  // ── 1. LOGIN PAGE (no auth needed) ───────────────────────────────────────
  {
    const page = await browser.newPage();
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto(`${BASE_URL}/login/`);
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${OUT_DIR}/login.png` });
    console.log('✓ login.png');
    await page.close();
  }

  // ── Authenticated desktop session (cookie injection) ─────────────────────
  const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  await ctx.addCookies(cookieDefs('localhost'));

  const page = await ctx.newPage();

  // ── 2. HOMEPAGE ──────────────────────────────────────────────────────────
  await page.goto(`${BASE_URL}/`);
  await page.waitForLoadState('networkidle');
  console.log('Homepage URL:', page.url());
  await page.screenshot({ path: `${OUT_DIR}/homepage.png` });
  console.log('✓ homepage.png');

  // ── 3. MEGA-MENU ─────────────────────────────────────────────────────────
  try {
    const trigger = await page.$('.site-nav-item.has-dropdown .site-nav-link') ||
                    await page.$('.site-nav-item:first-child .site-nav-link');
    if (trigger) {
      await trigger.hover();
      await page.waitForTimeout(600);
      await page.screenshot({ path: `${OUT_DIR}/mega-menu.png` });
      console.log('✓ mega-menu.png');
    } else {
      console.error('mega-menu: no nav trigger found');
    }
  } catch (e) {
    console.error('mega-menu:', e.message);
  }
  await page.mouse.move(0, 0);
  await page.waitForTimeout(300);

  // ── 4. CHANNEL PAGE (L1 — card grid) ─────────────────────────────────────
  await page.goto(CHANNEL_URL);
  await page.waitForLoadState('networkidle');
  console.log('Channel URL:', page.url());
  await page.screenshot({ path: `${OUT_DIR}/channel-page.png` });
  console.log('✓ channel-page.png');

  // ── 5. CONTENT PAGE (L3 — sidebar + anchor bar) ───────────────────────────
  await page.goto(CONTENT_URL);
  await page.waitForLoadState('networkidle');
  console.log('Content URL:', page.url());
  await page.screenshot({ path: `${OUT_DIR}/content-page.png` });
  console.log('✓ content-page.png');

  // ── 6. SEARCH ────────────────────────────────────────────────────────────
  await page.goto(SEARCH_URL);
  await page.waitForLoadState('networkidle');
  console.log('Search URL:', page.url());
  await page.screenshot({ path: `${OUT_DIR}/search.png` });
  console.log('✓ search.png');

  await ctx.close();

  // ── 7. MOBILE MENU ────────────────────────────────────────────────────────
  const mobileCtx = await browser.newContext({ viewport: { width: 390, height: 844 } });
  await mobileCtx.addCookies(cookieDefs('localhost'));
  const mobilePage = await mobileCtx.newPage();

  await mobilePage.goto(`${BASE_URL}/`);
  await mobilePage.waitForLoadState('networkidle');
  try {
    await mobilePage.click('.mobile-menu-trigger');
    await mobilePage.waitForTimeout(700);
    await mobilePage.screenshot({ path: `${OUT_DIR}/mobile.png` });
    console.log('✓ mobile.png');
  } catch (e) {
    await mobilePage.screenshot({ path: `${OUT_DIR}/mobile.png` });
    console.error('mobile:', e.message);
  }
  await mobileCtx.close();

  // ── 8. WP ADMIN (ACF block editor) ────────────────────────────────────────
  const adminCtx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  await adminCtx.addCookies(cookieDefs('localhost'));
  const adminPage = await adminCtx.newPage();

  try {
    // Navigate to the pages list first — use domcontentloaded to avoid
    // waiting on external wordpress.org update-check requests that hang on localhost
    await adminPage.goto(`${BASE_URL}/wp-admin/edit.php?post_type=page`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await adminPage.waitForTimeout(1500);
    console.log('WP admin pages list:', adminPage.url());

    if (adminPage.url().includes('wp-admin')) {
      // Find a page that uses the generic-content template (has ACF blocks)
      // Look for any page in the list and open its editor
      const editLinks = await adminPage.$$eval(
        '.wp-list-table tbody tr td.title a.row-title',
        els => els.map(a => a.href),
      );
      console.log('Pages found:', editLinks.length, editLinks.slice(0, 3));

      // Prefer a content page (not login, homepage, etc.) that likely has ACF blocks
      const contentPage = editLinks.find(url =>
        !url.includes('login') && !url.includes('homepage') && !url.includes('invite'),
      ) || editLinks[0];

      if (contentPage) {
        await adminPage.goto(contentPage);
        await adminPage.waitForLoadState('networkidle');
        // Wait for the block editor or ACF fields to render
        await adminPage.waitForTimeout(2000);
        await adminPage.screenshot({ path: `${OUT_DIR}/wp-admin.png`, fullPage: false });
        console.log('✓ wp-admin.png —', contentPage);
      } else {
        await adminPage.screenshot({ path: `${OUT_DIR}/wp-admin.png` });
        console.log('✓ wp-admin.png (pages list)');
      }
    } else {
      await adminPage.screenshot({ path: `${OUT_DIR}/wp-admin.png` });
      console.log('wp-admin redirected to:', adminPage.url());
    }
  } catch (e) {
    await adminPage.screenshot({ path: `${OUT_DIR}/wp-admin.png` });
    console.error('wp-admin:', e.message);
  }
  await adminCtx.close();

  await browser.close();
  console.log('\nAll screenshots saved to', OUT_DIR);
}

run().catch(err => {
  console.error(err);
  process.exit(1);
});
