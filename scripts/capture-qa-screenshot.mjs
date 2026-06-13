/**
 * QA 比較用フルページスクショ（指定 viewport 幅）
 *
 * 用法:
 *   node scripts/capture-qa-screenshot.mjs \
 *     --url 'http://example.local/about/' \
 *     --width 1920 \
 *     --out qa-screenshots/about/1920.png
 */
import { mkdir } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { chromium } from 'playwright';

const args = process.argv.slice(2);

function getArg(name) {
  const index = args.indexOf(`--${name}`);
  if (index === -1 || !args[index + 1]) {
    throw new Error(`--${name} が必要です`);
  }
  return args[index + 1];
}

function getOptionalArg(name) {
  const index = args.indexOf(`--${name}`);
  if (index === -1 || !args[index + 1]) return null;
  return args[index + 1];
}

const url = getArg('url');
const width = Number(getArg('width'));
const out = resolve(getArg('out'));
const basicUser = getOptionalArg('user');
const basicPass = getOptionalArg('pass');
const httpCredentials = (basicUser && basicPass)
  ? { username: basicUser, password: basicPass }
  : undefined;

if (!Number.isFinite(width) || width <= 0) {
  throw new Error('--width は正の数値で指定してください');
}

const hideAdminCss = `
  #wpadminbar,
  #query-monitor-main,
  #qm-icon-container,
  .query-monitor {
    display: none !important;
  }
  html { margin-top: 0 !important; }
  body.admin-bar {
    margin-top: 0 !important;
    padding-top: 0 !important;
  }
`;

await mkdir(dirname(out), { recursive: true });

const browser = await chromium.launch();
const page = await browser.newPage({
  viewport: {
    width,
    height: width <= 480 ? 844 : 1080,
  },
  deviceScaleFactor: 1,
  ...(httpCredentials && { httpCredentials }),
});

await page.emulateMedia({ reducedMotion: 'reduce' });
await page.goto(url, { waitUntil: 'networkidle' });
await page.addStyleTag({ content: hideAdminCss });
await page.evaluate(async () => {
  await new Promise(resolve => {
    const step = () => {
      window.scrollBy(0, 400);
      if (window.scrollY + window.innerHeight < document.body.scrollHeight) {
        setTimeout(step, 80);
      } else {
        window.scrollTo(0, 0);
        resolve();
      }
    };
    step();
  });
});
await page.evaluate(() => document.fonts.ready);

await page.screenshot({
  path: out,
  fullPage: true,
});

await browser.close();

const size = await import('node:fs/promises').then((fs) => fs.readFile(out));
console.log(`saved: ${out} (${width}px viewport, ${size.byteLength} bytes)`);
