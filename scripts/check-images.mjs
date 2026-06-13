// dist/assets/images/ の画像ファイルサイズを一覧し、1MB 超をフラグする
// 基準: wiki/coding-ejs-html.md §ファイルサイズの基準

import { readdirSync, statSync, existsSync } from 'node:fs'
import { join, extname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = fileURLToPath(new URL('.', import.meta.url))
const ROOT = resolve(__dirname, '..')
const IMAGE_DIR = join(ROOT, 'dist', 'assets', 'images')
const IMAGE_EXTS = new Set(['.jpg', '.jpeg', '.png', '.gif', '.webp', '.avif', '.svg'])
const LIMIT = 1024 * 1024 // 1MB

if (!existsSync(IMAGE_DIR)) {
  console.error('❌  dist/assets/images が見つかりません。先に npm run build を実行してください。')
  process.exit(1)
}

function collect(dir) {
  const results = []
  for (const entry of readdirSync(dir, { withFileTypes: true })) {
    const full = join(dir, entry.name)
    if (entry.isDirectory()) {
      results.push(...collect(full))
    } else if (IMAGE_EXTS.has(extname(entry.name).toLowerCase())) {
      results.push({ name: entry.name, size: statSync(full).size })
    }
  }
  return results
}

const files = collect(IMAGE_DIR).sort((a, b) => b.size - a.size)
const over = files.filter(f => f.size > LIMIT)

console.log(`\n📸  画像ファイルサイズ チェック  （基準: 1MB 以内）`)
console.log(`    対象 ${files.length} ファイル  /  1MB 超過 ${over.length} 件\n`)

for (const f of files) {
  const kb = (f.size / 1024).toFixed(1).padStart(8)
  const flag = f.size > LIMIT ? '❌' : '✅'
  console.log(`  ${flag}  ${f.name.padEnd(50)} ${kb} KB`)
}

if (over.length > 0) {
  console.log(`\n⚠️   上記 ${over.length} 件を確認し、例外に該当するかを案件メモに記録してください。`)
  console.log('    基準・例外の考え方: wiki/coding-ejs-html.md §ファイルサイズの基準\n')
  process.exit(1)
} else {
  console.log('\n✅  すべて 1MB 以内\n')
}
