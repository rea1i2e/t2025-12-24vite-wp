#!/usr/bin/env node
/**
 * FTP_EXCLUDE（deploy.yml / deploy-production.yml）と
 * functions-lib/func-ai1wm-exclude.php の除外リストのズレを一覧する。
 *
 * - 片方にしか無い項目を出すだけ（自動修正しない）
 * - グロブでしか書けない FTP 項目（*.map / .env* 等）は照合対象外
 * - ズレが無いとき「なし」を出して exit 0。ズレがあれば exit 1
 *
 * Usage: node scripts/check-exclude-parity.mjs
 *    or: npm run check:exclude-parity
 */

import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const DEPLOY_YML = path.join(ROOT, '.github/workflows/deploy.yml');
const DEPLOY_PROD_YML = path.join(ROOT, '.github/workflows/deploy-production.yml');
const AI1WM_PHP = path.join(ROOT, 'functions-lib/func-ai1wm-exclude.php');

/** FTP 側だけで照合対象外にするパターン（グロブ必須など） */
const FTP_GLOB_ONLY = new Set([
  '**/*.map',
  '**/.env*',
  '**/vite.config.*',
  '**/postcss.config.*',
  '**/.prod-deploy-baseline.json',
  '**/.ftp-deploy-sync-state.json',
]);

/**
 * FTP micromatch 行 → 比較用キー（ディレクトリ／ファイル名）
 * 例: **\/src/** → src, **\/README.md → README.md, **\/.git* → .git*
 */
export function normalizeFtpPattern(raw) {
  let p = String(raw).trim();
  if (!p || p.startsWith('#')) return null;
  p = p.replace(/^\*\*\//, '').replace(/\/\*\*$/, '').replace(/\/$/, '');
  return p || null;
}

/** AI1WM の `$exclude_filters[] = $theme_dir . '/foo'` → foo（コメント行は除外） */
export function parseAi1wmEntries(phpText) {
  const out = new Set();
  const re = /\$exclude_filters\s*\[\s*\]\s*=\s*\$theme_dir\s*\.\s*'\/([^']+)'/;
  for (const line of String(phpText).split(/\r?\n/)) {
    const trimmed = line.trim();
    if (trimmed.startsWith('//') || trimmed.startsWith('*') || trimmed.startsWith('/*')) continue;
    const m = trimmed.match(re);
    if (m) out.add(m[1].replace(/\/$/, ''));
  }
  return out;
}

/** AI1WM の具体パスが FTP グロブ専用パターンでカバーされているか */
export function coveredByFtpGlob(ai1wmRel, ftpGlobPatterns) {
  const name = ai1wmRel.split('/').pop() || ai1wmRel;
  for (const g of ftpGlobPatterns) {
    if (g === '**/*.map' && name.endsWith('.map')) return true;
    if (g === '**/.env*' && (name.startsWith('.env') || ai1wmRel.includes('/.env'))) return true;
    if (g === '**/vite.config.*' && name.startsWith('vite.config.')) return true;
    if (g === '**/postcss.config.*' && name.startsWith('postcss.config.')) return true;
    if (g === '**/.prod-deploy-baseline.json' && name === '.prod-deploy-baseline.json') return true;
    if (g === '**/.ftp-deploy-sync-state.json' && name === '.ftp-deploy-sync-state.json') return true;
  }
  return false;
}

export function parseFtpExcludeBlock(yamlText) {
  const lines = String(yamlText).split(/\r?\n/);
  const start = lines.findIndex((l) => /^\s*FTP_EXCLUDE:\s*\|\s*$/.test(l));
  if (start < 0) {
    throw new Error('FTP_EXCLUDE ブロックが見つかりません');
  }
  const patterns = [];
  for (let i = start + 1; i < lines.length; i += 1) {
    const line = lines[i];
    if (/^\S/.test(line) || /^[A-Za-z0-9_]+:/.test(line.trim())) break;
    if (/^\s+\S/.test(line)) {
      patterns.push(line.replace(/^\s+/, '').trim());
    }
  }
  return patterns;
}

/**
 * FTP の .git* 系は AI1WM の .git / .gitignore / .gitattributes / .github をまとめてカバーする想定。
 * 比較では FTP `.git*` と AI1WM の個別パスを相互に「カバー済み」とみなす。
 */
const GIT_STAR_COVERS = new Set(['.git', '.gitignore', '.gitattributes', '.github']);

function ftpKeysFromPatterns(patterns) {
  const keys = new Set();
  const skippedGlob = [];
  for (const raw of patterns) {
    if (FTP_GLOB_ONLY.has(raw.trim())) {
      skippedGlob.push(raw.trim());
      continue;
    }
    const key = normalizeFtpPattern(raw);
    if (key) keys.add(key);
  }
  return { keys, skippedGlob };
}

function coveredByOther(ftpKeys, ai1wmKeys, skippedGlob) {
  const ftpOnly = [];
  const ai1wmOnly = [];

  for (const k of [...ftpKeys].sort()) {
    if (ai1wmKeys.has(k)) continue;
    if (k === '.git*') {
      const any = [...GIT_STAR_COVERS].some((g) => ai1wmKeys.has(g));
      if (any) continue;
    }
    ftpOnly.push(k);
  }

  for (const k of [...ai1wmKeys].sort()) {
    if (ftpKeys.has(k)) continue;
    if (GIT_STAR_COVERS.has(k) && ftpKeys.has('.git*')) continue;
    if (coveredByFtpGlob(k, skippedGlob)) continue;
    ai1wmOnly.push(k);
  }

  return { ftpOnly, ai1wmOnly };
}

export async function runCheck({ root = ROOT } = {}) {
  const deploy = await readFile(path.join(root, '.github/workflows/deploy.yml'), 'utf8');
  const prod = await readFile(path.join(root, '.github/workflows/deploy-production.yml'), 'utf8');
  const php = await readFile(path.join(root, 'functions-lib/func-ai1wm-exclude.php'), 'utf8');

  const deployPatterns = parseFtpExcludeBlock(deploy);
  const prodPatterns = parseFtpExcludeBlock(prod);

  const report = [];
  let hasDiff = false;

  if (deployPatterns.join('\n') !== prodPatterns.join('\n')) {
    hasDiff = true;
    report.push('deploy.yml と deploy-production.yml の FTP_EXCLUDE が一致しません。');
    const d = new Set(deployPatterns.map((p) => p.trim()).filter(Boolean));
    const p = new Set(prodPatterns.map((x) => x.trim()).filter(Boolean));
    for (const x of [...d].sort()) {
      if (!p.has(x)) report.push(`  - production に無い: ${x}`);
    }
    for (const x of [...p].sort()) {
      if (!d.has(x)) report.push(`  - deploy.yml に無い: ${x}`);
    }
  }

  const { keys: ftpKeys, skippedGlob } = ftpKeysFromPatterns(deployPatterns);
  const ai1wmKeys = parseAi1wmEntries(php);
  const { ftpOnly, ai1wmOnly } = coveredByOther(ftpKeys, ai1wmKeys, skippedGlob);

  if (ftpOnly.length === 0 && ai1wmOnly.length === 0 && !hasDiff) {
    return { ok: true, lines: ['なし'], skippedGlob };
  }

  const lines = [...report];
  if (ftpOnly.length || ai1wmOnly.length) {
    lines.push('FTP_EXCLUDE と AI1WM のズレ:');
    for (const k of ftpOnly) lines.push(`  - FTP_EXCLUDE にだけある: ${k}`);
    for (const k of ai1wmOnly) lines.push(`  - AI1WM にだけある: ${k}`);
  }
  if (skippedGlob.length) {
    lines.push('照合対象外（FTP グロブのみ）:');
    for (const g of skippedGlob) lines.push(`  - ${g}`);
  }
  return { ok: false, lines, skippedGlob, ftpOnly, ai1wmOnly };
}

const isDirect = process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url);
if (isDirect) {
  runCheck()
    .then((result) => {
      for (const line of result.lines) console.log(line);
      process.exitCode = result.ok ? 0 : 1;
    })
    .catch((err) => {
      console.error(`ERROR: ${err.message || err}`);
      process.exitCode = 1;
    });
}
