const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch({ headless: true });
  const p = await b.newPage();
  await p.goto('http://localhost:8881/wp-login.php', { waitUntil: 'domcontentloaded', timeout: 30000 });
  console.log('URL:', p.url());
  const body = await p.textContent('body').catch(() => 'error');
  console.log('Body snippet:', body.replace(/\s+/g, ' ').slice(0, 500));
  const inputs = await p.$$eval('input', els => els.map(e => ({ type: e.type, name: e.name, id: e.id })));
  console.log('Inputs:', JSON.stringify(inputs));
  await p.screenshot({ path: 'screenshots/wp-login-check.png' });
  await b.close();
})().catch(e => { console.error(e.message); process.exit(1); });
