// dist/assets/css/*.css の background に image-set（avif → webp → 元）を付与
// 正本: config/theme-build.config.js

import { readFileSync, writeFileSync, existsSync, readdirSync } from 'node:fs'
import { join, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'
import { themeBuildConfig } from '../config/theme-build.config.js'

const __dirname = fileURLToPath(new URL('.', import.meta.url))
const ROOT = resolve(__dirname, '..')
const DIST = join(ROOT, 'dist')

const imageAltFormats = themeBuildConfig.imageAltFormats
const injectFormats =
  imageAltFormats === 'none'
    ? []
    : imageAltFormats === 'webp'
      ? ['webp']
      : imageAltFormats === 'avif'
        ? ['avif']
        : ['avif', 'webp']
const injectWebp = injectFormats.includes('webp')
const injectAvif = injectFormats.includes('avif')

if (injectFormats.length === 0) {
  process.exit(0)
}

const cssDir = join(DIST, 'assets', 'css')
const distImagesDir = join(DIST, 'assets', 'images')
if (!existsSync(cssDir) || !existsSync(distImagesDir)) {
  process.exit(0)
}

const cssFiles = readdirSync(cssDir).filter((n) => n.toLowerCase().endsWith('.css'))
const imageFiles = readdirSync(distImagesDir)
const imageExt = /\.(jpe?g|png)$/i
const hashedBase = /^(.+)-[a-zA-Z0-9]+\.(jpe?g|png)$/i

const resolveBgImage = (pathInUrl) => {
  if (!imageExt.test(pathInUrl)) return null
  let base
  let jpgFile
  if (pathInUrl.includes('/')) {
    base = pathInUrl.replace(/^.*\//, '').replace(imageExt, '')
    jpgFile = imageFiles.find((f) => f.startsWith(base + '-') && imageExt.test(f))
    if (!jpgFile) {
      jpgFile = imageFiles.find((f) => f === pathInUrl || f.endsWith('/' + pathInUrl))
    }
    if (!jpgFile && imageFiles.includes(pathInUrl)) jpgFile = pathInUrl
  } else {
    const m = pathInUrl.match(hashedBase)
    base = m ? m[1] : pathInUrl.replace(imageExt, '')
    jpgFile = imageFiles.includes(pathInUrl) ? pathInUrl : imageFiles.find((f) => f === pathInUrl)
    if (!jpgFile) {
      jpgFile = imageFiles.find((f) => {
        const bm = f.match(hashedBase)
        return bm && bm[1] === base && imageExt.test(f)
      })
    }
    if (!jpgFile) {
      jpgFile = imageFiles.find((f) => f.startsWith(base + '.') && imageExt.test(f))
    }
  }
  if (!jpgFile) return null
  const webpFile = injectWebp
    ? imageFiles.find(
        (f) =>
          (f.startsWith(base + '-') || f.startsWith(base + '.')) &&
          f.toLowerCase().endsWith('.webp')
      )
    : null
  const avifFile = injectAvif
    ? imageFiles.find(
        (f) =>
          (f.startsWith(base + '-') || f.startsWith(base + '.')) &&
          f.toLowerCase().endsWith('.avif')
      )
    : null
  return { base, jpgFile, webpFile, avifFile }
}

const mimeForFile = (file) => {
  if (/\.png$/i.test(file)) return 'image/png'
  return 'image/jpeg'
}

const buildImageSet = (prefix, r) => {
  const parts = []
  if (r.avifFile) parts.push(`url(${prefix}${r.avifFile}) type("image/avif")`)
  if (r.webpFile) parts.push(`url(${prefix}${r.webpFile}) type("image/webp")`)
  parts.push(`url(${prefix}${r.jpgFile}) type("${mimeForFile(r.jpgFile)}")`)
  if (parts.length <= 1) return null
  return `image-set(${parts.join(',')})`
}

for (const cssFile of cssFiles) {
  const cssPath = join(cssDir, cssFile)
  let css = readFileSync(cssPath, 'utf8')
  const bgImagePattern = /background-image:\s*url\((\.\.\/images\/)([^)]+)\)\s*([;}])/g
  const bgShorthandPattern = /background:\s*url\((\.\.\/images\/)([^)]+)\)\s*([^;}]*)([;}])/g
  const newCss = css
    .replace(bgImagePattern, (match, prefix, pathInUrl, terminator) => {
      const r = resolveBgImage(pathInUrl)
      const imageSet = r ? buildImageSet(prefix, r) : null
      if (!imageSet) return match
      return `background-image:url(${prefix}${r.jpgFile});background-image:${imageSet};${terminator === '}' ? '}' : ';'}`
    })
    .replace(bgShorthandPattern, (match, prefix, pathInUrl, rest, terminator) => {
      const r = resolveBgImage(pathInUrl)
      const imageSet = r ? buildImageSet(prefix, r) : null
      if (!imageSet) return match
      if (terminator === '}') {
        return `background:url(${prefix}${r.jpgFile})${rest};background-image:${imageSet};}`
      }
      return `background:url(${prefix}${r.jpgFile})${rest};background-image:${imageSet};`
    })
  if (newCss !== css) writeFileSync(cssPath, newCss)
}
