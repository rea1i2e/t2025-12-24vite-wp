/**
 * 不要コードチェック
 * 使い方: npm run check:unused
 */

import { readFileSync, readdirSync, existsSync } from 'node:fs';
import { join, extname, relative } from 'node:path';

const ROOT = process.cwd();

// ── ヘルパー ──────────────────────────────────────────────────────────────────

function getFiles(dir, exts) {
  if (!existsSync(dir)) return [];
  const out = [];
  for (const entry of readdirSync(dir, { withFileTypes: true })) {
    const p = join(dir, entry.name);
    if (entry.isDirectory()) {
      if (['node_modules', 'dist', '.git', '.vite'].includes(entry.name)) continue;
      out.push(...getFiles(p, exts));
    } else if (entry.isFile() && exts.includes(extname(entry.name))) {
      out.push(p);
    }
  }
  return out;
}

function read(path) {
  try { return readFileSync(path, 'utf-8'); } catch { return ''; }
}

function rel(p) { return relative(ROOT, p); }

function lineNum(content, index) {
  return content.slice(0, index).split('\n').length;
}

// ── A. コメントアウトされたSCSS ──────────────────────────────────────────────

function checkCommentedScss() {
  // CSSコード的なパターンを含む行のみ（説明コメントは除外）
  const CSS_CODE_RE = /^\s*\/\/\s*(@?(function|mixin|include|return|if|else|each|for)\b|[\w-]+\s*:\s+|[.#&]\w|[{}]|@media\b|@supports\b)/;
  const findings = [];
  for (const file of getFiles(join(ROOT, 'src/assets/sass'), ['.scss'])) {
    const lines = read(file).split('\n');
    lines.forEach((line, i) => {
      if (CSS_CODE_RE.test(line)) {
        findings.push({ file: rel(file), line: i + 1, content: line.trim() });
      }
    });
  }
  return findings;
}

// ── B. 未使用の画像 ──────────────────────────────────────────────────────────

function checkUnusedImages() {
  const imgDir = join(ROOT, 'src/assets/images');
  if (!existsSync(imgDir)) return [];

  const sources = [
    ...getFiles(ROOT, ['.php', '.html']).filter(f => !f.includes('/tools/')),
    ...getFiles(join(ROOT, 'src'), ['.scss', '.js', '.mjs']),
  ];
  const allContent = sources.map(read).join('\n');

  const findings = [];
  function scan(dir) {
    for (const entry of readdirSync(dir, { withFileTypes: true })) {
      const p = join(dir, entry.name);
      if (entry.isDirectory()) { scan(p); continue; }
      if (!entry.isFile() || entry.name.startsWith('.')) continue;
      const name = entry.name;
      const nameNoExt = name.replace(/\.[^.]+$/, '');
      if (!allContent.includes(name) && !allContent.includes(nameNoExt)) {
        findings.push(rel(p));
      }
    }
  }
  scan(imgDir);
  return findings;
}

// ── C. 未使用のJSインポート ──────────────────────────────────────────────────

function checkUnusedJsImports() {
  const NAMED_RE = /^import\s+\{([^}]+)\}\s+from\s+['"][^'"]+['"]/gm;
  const DEFAULT_RE = /^import\s+(\w+)\s+from\s+['"][^'"]+['"]/gm;
  const findings = [];

  for (const file of getFiles(join(ROOT, 'src/assets/js'), ['.js', '.mjs'])) {
    const content = read(file);

    for (const match of content.matchAll(NAMED_RE)) {
      const rest = content.replace(match[0], '');
      for (const raw of match[1].split(',')) {
        const name = raw.trim().replace(/\s+as\s+\w+/, '').trim();
        if (!name) continue;
        if (!new RegExp(`\\b${name}\\b`).test(rest)) {
          findings.push({ file: rel(file), line: lineNum(content, match.index), name });
        }
      }
    }

    for (const match of content.matchAll(DEFAULT_RE)) {
      const name = match[1];
      const rest = content.replace(match[0], '');
      if (!new RegExp(`\\b${name}\\b`).test(rest)) {
        findings.push({ file: rel(file), line: lineNum(content, match.index), name });
      }
    }
  }
  return findings;
}

// ── D. PHPに出てこないSCSSルートクラス ──────────────────────────────────────

function checkOrphanedScssSelectors() {
  const componentsDir = join(ROOT, 'src/assets/sass/components');
  if (!existsSync(componentsDir)) return [];

  const phpFiles = getFiles(ROOT, ['.php', '.html'])
    .filter(f => !f.includes('/tools/') && !f.includes('/dist/'));
  const allPhpContent = phpFiles.map(read).join('\n');

  // 行頭（インデントなし）のクラスセレクタのみ対象
  const ROOT_SEL_RE = /^(\.[a-z][a-z0-9-]*)\s*[{,]/gm;
  const SKIP_RE = /^(is-|js-|has-)/;
  const seen = new Set();
  const findings = [];

  for (const file of getFiles(componentsDir, ['.scss'])) {
    const content = read(file);
    for (const match of content.matchAll(ROOT_SEL_RE)) {
      const cls = match[1].slice(1);
      if (seen.has(cls)) continue;
      seen.add(cls);
      if (SKIP_RE.test(cls) || cls.includes('__') || cls.includes('--')) continue;
      if (!allPhpContent.includes(cls)) {
        findings.push({ file: rel(file), line: lineNum(content, match.index), selector: match[1] });
      }
    }
  }
  return findings;
}

// ── レポート出力 ─────────────────────────────────────────────────────────────

const HR = '─'.repeat(64);

function section(title, items, format) {
  console.log(`\n${HR}`);
  console.log(` ${title}`);
  console.log(HR);
  if (items.length === 0) {
    console.log('  なし');
  } else {
    items.forEach(format);
  }
}

section(
  'A. コメントアウトされたSCSS',
  checkCommentedScss(),
  ({ file, line, content }) => {
    console.log(`  ${file}:${line}`);
    console.log(`    ${content.slice(0, 80)}`);
  }
);

section(
  'B. 未使用の画像',
  checkUnusedImages(),
  f => console.log(`  ${f}`)
);

section(
  'C. 未使用のJSインポート',
  checkUnusedJsImports(),
  ({ file, line, name }) => console.log(`  ${file}:${line}  → ${name}`)
);

section(
  'D. PHPに出てこないSCSSクラス（components/）',
  checkOrphanedScssSelectors(),
  ({ file, line, selector }) => console.log(`  ${file}:${line}  ${selector}`)
);

console.log(`\n${HR}\n`);
