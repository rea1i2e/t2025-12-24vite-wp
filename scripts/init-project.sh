#!/usr/bin/env bash
# 案件着手時にデモ・仮アセット・任意サンプルを一括削除する（WP テンプレ向け）
# 使用方法: npm run init
#
# 方針: 必ず使う土台以外は消し、必要なら WP テンプレ原本 / 静的テンプレ（型録）から複製する。
# テンプレ本体（デモカタログを残すリポ）では実行しないこと。

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "デモ・仮アセットを削除します... ROOT=${ROOT}"

# -------------------------------------------------------
# 1. ディレクトリ削除
# -------------------------------------------------------
dirs=(
  "components-demo"
  "src/assets/sass/components-demo"
  "src/assets/images/demo"
  "src/assets/images/common"
)

for dir in "${dirs[@]}"; do
  target="$ROOT/$dir"
  if [ -d "$target" ]; then
    rm -rf "$target"
    echo "  削除: $dir"
  else
    echo "  スキップ（無し）: $dir"
  fi
done

# -------------------------------------------------------
# 2. 任意サンプル PHP / デモ JS の削除
# -------------------------------------------------------
files=(
  "functions-lib/func-posts-ajax-load-more.php"
  "functions-lib/func-set-posttype-works.php"
  "src/assets/js/_archive-load-more.js"
  "src/assets/js/_splide-fade.js"
  "src/assets/js/_splide-loop.js"
  "src/assets/js/_splide-thumbnail.js"
  "src/assets/js/_tab.js"
  "src/assets/js/_dialog.js"
  "src/assets/js/_accordion.js"
  "src/assets/js/_option-color.js"
  "src/assets/js/_checkFormValidity.js"
  "src/assets/js/_flatpickr.js"
  "src/assets/js/_email-protection.js"
  "src/assets/js/_nav-current-section.js"
  "src/assets/js/_modal.js"
  "src/assets/js/_counter.js"
  "src/assets/js/_header.js"
)

for file in "${files[@]}"; do
  target="$ROOT/$file"
  if [ -f "$target" ]; then
    rm -f "$target"
    echo "  削除: $file"
  fi
done

# -------------------------------------------------------
# 3. style.scss: components-demo の @use 行を削除
# -------------------------------------------------------
STYLE_SCSS="$ROOT/src/assets/sass/style.scss"
if [ -f "$STYLE_SCSS" ] && grep -q 'components-demo' "$STYLE_SCSS"; then
  sed -i '' '/components-demo/d' "$STYLE_SCSS"
  echo "  更新: src/assets/sass/style.scss"
fi

# -------------------------------------------------------
# 4. Sass: images/common への url() をコメントアウト（ビルド切れ防止）
# -------------------------------------------------------
ROOT_PATH="$ROOT" node --input-type=module << 'JSEOF'
import { readFileSync, writeFileSync, readdirSync, statSync } from 'fs';
import { join, relative } from 'path';

const root = process.env.ROOT_PATH;

function walk(dir, exts, out = []) {
  if (!statSync(dir, { throwIfNoEntry: false })?.isDirectory()) return out;
  for (const name of readdirSync(dir)) {
    if (['node_modules', 'dist', '.git'].includes(name)) continue;
    const p = join(dir, name);
    const st = statSync(p);
    if (st.isDirectory()) walk(p, exts, out);
    else if (exts.some((e) => name.endsWith(e))) out.push(p);
  }
  return out;
}

let n = 0;
for (const file of walk(join(root, 'src/assets/sass'), ['.scss'])) {
  if (file.includes('/components-demo/')) continue;
  const before = readFileSync(file, 'utf8');
  if (!before.includes('images/common')) continue;
  const after = before
    .split('\n')
    .map((line) => {
      if (line.includes('images/common') && !/^\s*\/\//.test(line) && !/^\s*\*/.test(line)) {
        const indent = line.match(/^\s*/)?.[0] ?? '';
        return `${indent}// ${line.trim()} /* init: common 仮アイコン削除 */`;
      }
      return line;
    })
    .join('\n');
  if (after !== before) {
    writeFileSync(file, after);
    console.log(`  更新: ${relative(root, file)} (common url コメントアウト)`);
    n++;
  }
}
console.log(`  Sass common 参照: ${n} ファイル`);
JSEOF

# -------------------------------------------------------
# 5. PHP: components-demo への get_template_part を除去
# -------------------------------------------------------
ROOT_PATH="$ROOT" node --input-type=module << 'JSEOF'
import { readFileSync, writeFileSync, readdirSync, statSync } from 'fs';
import { join, relative } from 'path';

const root = process.env.ROOT_PATH;

function walkPhp(dir, out = []) {
  for (const name of readdirSync(dir)) {
    if (['node_modules', 'dist', '.git', 'vendor'].includes(name)) continue;
    const p = join(dir, name);
    const st = statSync(p);
    if (st.isDirectory()) walkPhp(p, out);
    else if (name.endsWith('.php')) out.push(p);
  }
  return out;
}

function stripDemoTemplateParts(src) {
  const re = /get_template_part\s*\(\s*['"]components-demo\//;
  let result = '';
  let i = 0;
  while (i < src.length) {
    const rest = src.slice(i);
    const m = rest.match(re);
    if (!m) {
      result += rest;
      break;
    }
    const start = i + m.index;
    result += src.slice(i, start);
    const openParen = src.indexOf('(', start);
    let depth = 0;
    let j = openParen;
    for (; j < src.length; j++) {
      const ch = src[j];
      if (ch === '(') depth++;
      else if (ch === ')') {
        depth--;
        if (depth === 0) {
          j++;
          break;
        }
      }
    }
    while (j < src.length && /\s/.test(src[j])) j++;
    if (src[j] === ';') j++;
    if (src[j] === '\n') j++;
    i = j;
  }
  result = result.replace(/[ \t]+\n/g, '\n');
  result = result.replace(/\n{3,}/g, '\n\n');
  return result;
}

let changed = 0;
for (const file of walkPhp(root)) {
  if (file.includes(`${root}/.cursor`) || file.includes(`${root}/ai-docs`)) continue;
  const before = readFileSync(file, 'utf8');
  if (!before.includes('components-demo')) continue;
  let after = stripDemoTemplateParts(before);
  after = after.replace(/<\?php\s*\?>\n?/g, '');
  if (after !== before) {
    writeFileSync(file, after);
    console.log(`  更新: ${relative(root, file)}`);
    changed++;
  }
}
console.log(`  PHP get_template_part 除去: ${changed} ファイル`);
JSEOF

# -------------------------------------------------------
# 6. front-page.php を最小構成へ
# -------------------------------------------------------
FRONT_PAGE="$ROOT/front-page.php"
if [ -f "$FRONT_PAGE" ]; then
  cat > "$FRONT_PAGE" << 'EOF'
<?php get_header(); ?>
<main class="p-main-top">
  <?php get_template_part('components/top-mv'); ?>
</main>
<?php get_footer(); ?>
EOF
  echo "  更新: front-page.php（最小構成）"
fi

TOP_MV="$ROOT/components/top-mv.php"
if [ -f "$TOP_MV" ]; then
  printf '%s\n' '<?php declare(strict_types=1); ?>' > "$TOP_MV"
  echo "  更新: components/top-mv.php"
fi

# -------------------------------------------------------
# 7. main.js を土台だけに
# -------------------------------------------------------
MAIN_JS="$ROOT/src/assets/js/main.js"
cat > "$MAIN_JS" << 'EOF'
/* 共通（ヘッダー等の土台） */
import './_initializeSmoothScroll.js';
import './_drawer.js';
import './_toggle.js';

// import './_header.js';

/* スクロールに応じた表示制御 */
import './_fadein.js';
import './_page-top.js';

/* 案件固有の JS は型録（静的テンプレ）または WP テンプレ原本から都度移植する */
EOF
echo "  更新: src/assets/js/main.js"

# -------------------------------------------------------
# 8. header.php: デモ preload / 仮ロゴ画像を外す
# -------------------------------------------------------
HEADER="$ROOT/header.php"
if [ -f "$HEADER" ]; then
  ROOT_PATH="$ROOT" node --input-type=module << 'JSEOF'
import { readFileSync, writeFileSync } from 'fs';
import { join } from 'path';
const root = process.env.ROOT_PATH;
const file = join(root, 'header.php');
let src = readFileSync(file, 'utf8');
const before = src;
// front-page デモ画像 preload ブロック除去
src = src.replace(/\n?\s*<\?php if \(is_front_page\(\)\) : \$preload_image_url = ty_theme_image_url\('demo\/[^']+'\); \?>\n\s*<link rel="preload" href="<\?php echo \$preload_image_url; \?>" as="image">\n\s*<\?php endif; \?>\n?/g, '\n');
// 仮ロゴ画像 → サイト名
src = src.replace(
  /<\?php ty_img\('common\/logo\.svg',\s*'',\s*true\); \?>/,
  "<?php echo esc_html(get_bloginfo('name')); ?>"
);
if (src !== before) {
  writeFileSync(file, src);
  console.log('  更新: header.php');
} else {
  console.log('  スキップ（変更なし）: header.php');
}
JSEOF
fi

# -------------------------------------------------------
# 9. thumbnail フォールバック（common/logo 依存を外す）
# -------------------------------------------------------
THUMB="$ROOT/functions-lib/func-thumbnail.php"
if [ -f "$THUMB" ] && grep -q "common/logo.svg" "$THUMB"; then
  ROOT_PATH="$ROOT" node --input-type=module << 'JSEOF'
import { readFileSync, writeFileSync } from 'fs';
import { join } from 'path';
const file = join(process.env.ROOT_PATH, 'functions-lib/func-thumbnail.php');
let src = readFileSync(file, 'utf8');
const re = /\/\/ フォールバック画像[\s\S]*?return \[[\s\S]*?\];\n\}/;
const replacement = `// 仮画像（common/logo.svg）は npm run init で削除する。
    // フォールバック用アセットを置くまでは出さない。
    return null;
}`;
if (re.test(src)) {
  src = src.replace(re, replacement);
  writeFileSync(file, src);
  console.log('  更新: functions-lib/func-thumbnail.php');
} else {
  console.log('  スキップ（パターン不一致）: functions-lib/func-thumbnail.php');
}
JSEOF
fi

# -------------------------------------------------------
# 10. ナビを最小構成へ（デモ・works 見本を除去）
# -------------------------------------------------------
NAV="$ROOT/functions-lib/func-nav-items.php"
if [ -f "$NAV" ]; then
  ROOT_PATH="$ROOT" node --input-type=module << 'JSEOF'
import { readFileSync, writeFileSync } from 'fs';
import { join } from 'path';
const file = join(process.env.ROOT_PATH, 'functions-lib/func-nav-items.php');
let src = readFileSync(file, 'utf8');
const re = /function ty_get_nav_items\(\): array \{\n\treturn \[[\s\S]*?\];\n\}/;
const replacement = `function ty_get_nav_items(): array {
	// デモ・制作実績見本は init で削除。案件の IA に合わせて差し替える。
	return [
		[
			'slug' => 'top',
			'text' => 'トップ',
		],
		[
			'slug' => 'contact',
			'text' => 'お問い合わせ',
			'modifier' => 'contact',
		],
	];
}`;
if (re.test(src)) {
  src = src.replace(re, replacement);
  writeFileSync(file, src);
  console.log('  更新: functions-lib/func-nav-items.php');
} else {
  console.log('  スキップ（パターン不一致）: functions-lib/func-nav-items.php');
}
JSEOF
fi

echo ""
echo "完了しました。"
echo "残している主な土台: Vite / functions ローダー / header・footer 枠 / drawer・toggle / 仮フォント"
echo "次のステップ:"
echo "  1. rg components-demo / images/common で参照ゼロを確認"
echo "  2. npm run build"
echo "  3. 必要なパーツは型録または WP テンプレ原本から複製"
