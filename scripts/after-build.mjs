// scripts/after-build.mjs
// HTMLを後処理して <img> を <picture> 化し、width/height を自動付与します。

import { readFileSync, writeFileSync, existsSync, readdirSync, statSync } from 'node:fs'
import { dirname, join, relative, resolve, sep } from 'node:path'
import { JSDOM } from 'jsdom'
import sharp from 'sharp'
import beautify from 'js-beautify'

const DIST = 'dist'

function listHtmlFiles(dir, out = []) {
  for (const name of readdirSync(dir)) {
    const fp = join(dir, name)
    const st = statSync(fp)
    if (st.isDirectory()) listHtmlFiles(fp, out)
    else if (name.toLowerCase().endsWith('.html')) out.push(fp)
  }
  // console.log('[after-build] html files:', out.map(p => p.replace(process.cwd() + '/', '')))
  return out
}

function toDistAbsFromHtml(htmlPath, src) {
  if (!src || /^https?:\/\//i.test(src) || /^data:/i.test(src)) return null
  if (src.startsWith('/')) return resolve(DIST, src.slice(1))
  return resolve(dirname(htmlPath), src)
}

function toDistRel(absPath) {
  const rel = relative(resolve(DIST), absPath).replaceAll('\\', '/')
  return rel.startsWith('.') ? null : rel
}

// foo-xxx.jpg -> [foo-xxx.webp, foo-xxx.jpg.webp]
function candidates(relPath, exts) {
  const baseNoExt = relPath.replace(/\.(jpe?g|png|gif)(?:\?.*)?$/i, '')
  const extMatch = relPath.match(/\.(jpe?g|png|gif)(?:\?.*)?$/i)
  const origExt = extMatch ? extMatch[0] : '' // 例: .jpg
  const out = []
  for (const ext of exts) {
    out.push(baseNoExt + '.' + ext)              // foo-xxx.webp
    if (origExt) out.push(relPath.replace(origExt, origExt + '.' + ext)) // foo-xxx.jpg.webp
  }
  return Array.from(new Set(out))
}

function firstExisting(relList) {
  for (const rel of relList) {
    const abs = resolve(DIST, rel)
    if (existsSync(abs)) return { rel, abs }
  }
  return null
}

function firstUrlFromSrcset(ss) {
  if (!ss) return ''
  const first = ss.split(',')[0].trim()
  const url = first.split(/\s+/)[0]
  return url || ''
}

function urlToRelFromHtml(htmlPath, url) {
  const abs = toDistAbsFromHtml(htmlPath, url)
  if (!abs) return null
  return toDistRel(abs)
}

async function metaForSrcsetUrl(ss, htmlPath) {
  const url = firstUrlFromSrcset(ss)
  if (!url) return null
  const abs = toDistAbsFromHtml(htmlPath, url)
  if (!abs || !existsSync(abs)) return null
  try { return await sharp(abs).metadata() } catch { return null }
}

function applySize(el, meta) {
  if (meta?.width && meta?.height) {
    el.setAttribute('width', String(meta.width))
    el.setAttribute('height', String(meta.height))
    // Debug log: confirm width/height applied to <source>
    // if (el.tagName && el.tagName.toLowerCase() === 'source') {
    //   console.log('    [debug] width/height set on <source>', el.getAttribute('type') || '', `${meta.width}x${meta.height}`)
    // }
  }
}

const htmlFiles = listHtmlFiles(DIST)
let converted = 0
let sizedOnly = 0

for (const htmlPath of htmlFiles) {
  const html = readFileSync(htmlPath, 'utf8')
  const dom = new JSDOM(html)
  const doc = dom.window.document

  const imgs = [...doc.querySelectorAll('img[src]:not([data-no-picture])')]
  // console.log(`[after-build] scanning ${htmlPath.replace(process.cwd() + '/', '')} (imgs: ${imgs.length})`)

  for (const img of imgs) {
    const srcAttr = img.getAttribute('src') || ''
    const insidePicture = !!(img.parentElement && img.parentElement.tagName && img.parentElement.tagName.toLowerCase() === 'picture')
    try {
      if (!/\.(jpe?g|png|gif)(?:\?.*)?$/i.test(srcAttr)) continue

      const abs = toDistAbsFromHtml(htmlPath, srcAttr)
      if (!abs || !existsSync(abs)) {
        // console.log('  - skip (not found):', srcAttr)
        continue
      }

      // width/height 自動付与（<picture> 内でも行う）
      let meta = null
      if (!img.hasAttribute('width') || !img.hasAttribute('height')) {
        meta = await sharp(abs).metadata().catch(() => null)
        if (meta?.width && meta?.height) {
          applySize(img, meta)
          // console.log('  - size set:', srcAttr, `${meta.width}x${meta.height}`)
        }
      } else {
        meta = await sharp(abs).metadata().catch(() => null)
      }

      // 既に <picture> 内なら、ラップはしないが WebP/AVIF の <source> を不足分だけ挿入する
      if (insidePicture) {
        const pictureEl = img.parentElement

        // 既存のタイプを把握
        const hasWebp = !!pictureEl.querySelector('source[type="image/webp"]')
        const hasAvif = !!pictureEl.querySelector('source[type="image/avif"]')

        const distRel = toDistRel(abs)
        if (distRel) {
          const webpCand = hasWebp ? null : candidates(distRel, ['webp'])
          const avifCand = hasAvif ? null : candidates(distRel, ['avif'])

          const foundWebp = webpCand ? firstExisting(webpCand) : null
          const foundAvif = avifCand ? firstExisting(avifCand) : null

          // 既存の art-direction（media 指定）を崩さないよう、フォールバック <img> の直前に差し込む
          if (foundAvif) {
            const s = doc.createElement('source')
            // 相対パスに変更（./プレフィックス付き）
            const relPath = relative(dirname(htmlPath), resolve(DIST, foundAvif.rel)).replaceAll('\\', '/')
            s.setAttribute('srcset', relPath.startsWith('.') ? relPath : './' + relPath)
            s.setAttribute('type', 'image/avif')
            // size from its own file (not from <img>)
            const ownMetaAvif = await metaForSrcsetUrl(relPath, htmlPath)
            applySize(s, ownMetaAvif)
            pictureEl.insertBefore(s, img)
            // console.log('  - inject AVIF into existing <picture>:', foundAvif.rel)
          }
          if (foundWebp) {
            const s = doc.createElement('source')
            // 相対パスに変更（./プレフィックス付き）
            const relPath = relative(dirname(htmlPath), resolve(DIST, foundWebp.rel)).replaceAll('\\', '/')
            s.setAttribute('srcset', relPath.startsWith('.') ? relPath : './' + relPath)
            s.setAttribute('type', 'image/webp')
            const ownMetaWebp = await metaForSrcsetUrl(relPath, htmlPath)
            applySize(s, ownMetaWebp)
            pictureEl.insertBefore(s, img)
            // console.log('  - inject WEBP into existing <picture>:', foundWebp.rel)
          }

          // For each existing JPG/PNG <source> (often art-direction with media),
          // add a corresponding WebP <source> with the SAME media if a webp asset exists
          const currentSources = Array.from(pictureEl.querySelectorAll('source'))
          for (const jpgSrc of currentSources) {
            const t = (jpgSrc.getAttribute('type') || '').toLowerCase()
            if (t === 'image/webp' || t === 'image/avif') continue
            const ss = jpgSrc.getAttribute('srcset') || ''
            const url = firstUrlFromSrcset(ss)
            if (!url) continue
            // only target raster types we can webp-ize
            if (!/\.(jpe?g|png)(?:\?.*)?$/i.test(url)) continue
            const mediaAttr = jpgSrc.getAttribute('media') || ''

            // find webp variant for this exact source
            const rel = urlToRelFromHtml(htmlPath, url)
            if (!rel) continue
            const cand = candidates(rel, ['webp'])
            const found = firstExisting(cand)
            if (!found) continue

            // check if a webp <source> with same media and same file already exists
            let existsSame = false
            for (const s of pictureEl.querySelectorAll('source[type="image/webp"]')) {
              const smedia = s.getAttribute('media') || ''
              const srel = urlToRelFromHtml(htmlPath, firstUrlFromSrcset(s.getAttribute('srcset') || ''))
              if (smedia === mediaAttr && srel === found.rel) { existsSame = true; break }
            }
            if (existsSame) continue

            // create and insert webp BEFORE the jpg source so browsers prefer it
            const s = doc.createElement('source')
            // 相対パスに変更（./プレフィックス付き）
            const relPath = relative(dirname(htmlPath), resolve(DIST, found.rel)).replaceAll('\\', '/')
            s.setAttribute('srcset', relPath.startsWith('.') ? relPath : './' + relPath)
            s.setAttribute('type', 'image/webp')
            if (mediaAttr) s.setAttribute('media', mediaAttr)
            const ownMeta = await metaForSrcsetUrl(relPath, htmlPath)
            applySize(s, ownMeta)
            pictureEl.insertBefore(s, jpgSrc)
            // console.log('  - add WEBP for media source:', found.rel, mediaAttr ? `media=${mediaAttr}` : '')
          }

          // 既存/新規すべての <source> について、自身が指すファイルの実寸を付与
          for (const sourceEl of pictureEl.querySelectorAll('source')) {
            const ss = sourceEl.getAttribute('srcset') || ''
            const ownMeta = await metaForSrcsetUrl(ss, htmlPath)
            applySize(sourceEl, ownMeta)
          }
        }

        // console.log('  - already inside <picture>, sized and injected if available:', srcAttr)
        sizedOnly++
        continue
      }

      const distRel = toDistRel(abs)
      if (!distRel) continue

      const webpCand = candidates(distRel, ['webp'])
      const avifCand = candidates(distRel, ['avif'])

      const foundWebp = firstExisting(webpCand)
      const foundAvif = firstExisting(avifCand)

      if (!foundWebp && !foundAvif) {
        sizedOnly++
        // console.log('  - no alt formats, keep <img>:', distRel)
        continue
      }

      const picture = doc.createElement('picture')
      if (foundAvif) {
        const s = doc.createElement('source')
        // 相対パスに変更（./プレフィックス付き）
        const relPath = relative(dirname(htmlPath), resolve(DIST, foundAvif.rel)).replaceAll('\\', '/')
        s.setAttribute('srcset', relPath.startsWith('.') ? relPath : './' + relPath)
        s.setAttribute('type', 'image/avif')
        const ownMetaAvif = await metaForSrcsetUrl(relPath, htmlPath)
        applySize(s, ownMetaAvif)
        picture.appendChild(s)
        // console.log('  - add AVIF:', foundAvif.rel)
      }
      if (foundWebp) {
        const s = doc.createElement('source')
        // 相対パスに変更（./プレフィックス付き）
        const relPath = relative(dirname(htmlPath), resolve(DIST, foundWebp.rel)).replaceAll('\\', '/')
        s.setAttribute('srcset', relPath.startsWith('.') ? relPath : './' + relPath)
        s.setAttribute('type', 'image/webp')
        const ownMetaWebp = await metaForSrcsetUrl(relPath, htmlPath)
        applySize(s, ownMetaWebp)
        picture.appendChild(s)
        // console.log('  - add WEBP:', foundWebp.rel)
      }

      for (const sourceEl of picture.querySelectorAll('source')) {
        const ss = sourceEl.getAttribute('srcset') || ''
        const ownMeta = await metaForSrcsetUrl(ss, htmlPath)
        applySize(sourceEl, ownMeta)
      }

      const cloned = img.cloneNode(false)
      applySize(cloned, meta)
      picture.appendChild(cloned)
      img.replaceWith(picture)
      converted++
      // console.log('  - replaced with <picture>:', srcAttr)
    } catch (e) {
      // console.warn('[after-build] skip:', srcAttr, e?.message || e)
    }
  }

  // シンプルな整形
  let out = dom.serialize()
  
  // ブール属性を簡潔な形式に変換（required="", checked="", disabled="" など）
  out = out.replace(/\s+(required|checked|disabled|readonly|multiple|selected|autofocus|autoplay|controls|loop|muted|novalidate|open|reversed|async|defer|hidden|ismap|itemscope|nomodule|playsinline|seamless|truespeed|crossorigin|inert)=""/g, ' $1')
    
  // <picture>タグの整形のみ
  out = out.replace(/(<picture[^>]*>)([\s\S]*?)(<\/picture>)/g, (m, open, inner, close) => {
    let formatted = inner
      .replace(/\s*(<source\b[^>]*>)/g, '\n    $1')
      .replace(/\s*(<img\b[^>]*>)/g, '\n    $1')
      .replace(/\n{2,}/g, '\n')
      .replace(/\n+\s*$/,'')
      .replace(/^\s*\n?/, '')
    return `${open}${formatted}\n  ${close}`
  })

  // js-beautifyで全体を整形
  out = beautify.html(out, {
    indent_size: 2,
    indent_char: ' ',
    max_preserve_newlines: 0, // 空行をなくす
    preserve_newlines: true,
    end_with_newline: true,
    wrap_line_length: 0
  })

  writeFileSync(htmlPath, out)
  // console.log('rewrote:', htmlPath.split(sep).slice(-2).join('/'))
}

// console.log(`[after-build] picture化: ${converted} / 寸法のみ付与: ${sizedOnly}`)