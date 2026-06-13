/**
 * QA スクリーンショット一括撮影
 *
 * capture-qa.config.json に定義したページ・サイズを全件撮影する。
 * baseUrl を省略すると package.json の name から http://{name}.local を使う。
 * 案件 ID が YYYY-MM-DDxx 形式のとき Basic 認証を自動導出して渡す。
 * 用法: npm run capture:qa:all
 */
import { readFileSync } from 'node:fs';
import { execSync } from 'node:child_process';

const config = JSON.parse(readFileSync('capture-qa.config.json', 'utf-8'));
const pkg = JSON.parse(readFileSync('package.json', 'utf-8'));

const baseUrl = config.baseUrl ?? `http://${pkg.name}.local`;
const { widths, pages } = config;

// 案件 ID (YYYY-MM-DDxx) から Basic 認証情報を導出する
// username = xx + MM + DD、password = xx + MM
function deriveBasicAuth(name) {
  const match = name.match(/^(\d{4})-(\d{2})-(\d{2})([a-z]{2})$/i);
  if (!match) return null;
  const [, , mm, dd, xx] = match;
  return { username: `${xx}${mm}${dd}`, password: `${xx}${mm}` };
}

const basicAuth = config.basicAuth ?? deriveBasicAuth(pkg.name);
const authArgs = basicAuth
  ? `--user "${basicAuth.username}" --pass "${basicAuth.password}"`
  : '';

if (basicAuth) {
  console.log(`Basic 認証: user=${basicAuth.username}`);
}

for (const { slug, path } of pages) {
  const url = `${baseUrl}/${path}`;
  for (const width of widths) {
    const out = `qa-screenshots/${slug}-${width}.png`;
    console.log(`撮影中: ${slug} ${width}px → ${out}`);
    execSync(
      `node scripts/capture-qa-screenshot.mjs --url "${url}" --width ${width} --out "${out}" ${authArgs}`,
      { stdio: 'inherit' }
    );
  }
}

console.log('\n完了: qa-screenshots/ を確認してください');
