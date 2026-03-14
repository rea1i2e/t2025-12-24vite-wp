/**
 * 依存パッケージのインポート
 */
import { defineConfig } from "vite";
import sassGlobImports from "vite-plugin-sass-glob-import";
import fs from "node:fs";
import path, { resolve } from "node:path";
import { fileURLToPath } from "node:url";
import imageSize from "image-size";
import viteImagemin from "@vheemstra/vite-plugin-imagemin";
import imageminMozjpeg from "imagemin-mozjpeg";
import imageminOptipng from "imagemin-optipng";
import imageminGifsicle from "imagemin-gifsicle";
import imageminSvgo from "imagemin-svgo";
import imageminWebp from "imagemin-webp";
// imagemin-gif2webp は CJS なので default import の互換に依存せず、名前空間受け取りにします
import gif2webpCjs from 'imagemin-gif2webp';
const imageminGif2webp = gif2webpCjs;

/**
 * パスと証明書ディレクトリの設定
 */
const __dirname = path.dirname(fileURLToPath(import.meta.url));
const certDir = path.resolve(__dirname, ".certs");

/**
 * HTTPS設定の取得
 * 環境変数またはデフォルトの証明書ファイルからHTTPS設定を読み込む
 */
function getHttpsConfig() {
  const keyPath =
    process.env.VITE_HTTPS_KEY || path.join(certDir, "localhost-key.pem");
  const certPath =
    process.env.VITE_HTTPS_CERT || path.join(certDir, "localhost.pem");
  if (!fs.existsSync(keyPath) || !fs.existsSync(certPath)) return false;
  return {
    key: fs.readFileSync(keyPath),
    cert: fs.readFileSync(certPath),
  };
}

/**
 * PHPテンプレートが変更されたときにブラウザをフルリロード（WordPress用）
 * 注意: PHPはホットリプレースできないため、代わりにブラウザのリロードをトリガーする
 */
function wpPhpFullReload() {
  return {
    name: "wp-php-full-reload",
    apply: "serve",
    configureServer(server) {
      const patterns = [
        "**/*.php",
      ];
      server.watcher.add(patterns);
      server.watcher.on("change", (file) => {
        if (!file.endsWith(".php")) return;
        server.ws.send({ type: "full-reload", path: "*" });
      });
    },
  };
}

/**
 * パスをPOSIX形式（スラッシュ区切り）に変換
 * Windowsのバックスラッシュをスラッシュに統一
 */
function toPosixPath(p) {
  return p.replaceAll("\\", "/");
}

/**
 * ディレクトリを再帰的に走査してファイル一覧を取得
 */
function listFilesRecursive(dir) {
  const out = [];
  if (!fs.existsSync(dir)) return out;
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const abs = path.join(dir, entry.name);
    if (entry.isDirectory()) out.push(...listFilesRecursive(abs));
    else if (entry.isFile()) out.push(abs);
  }
  return out;
}

/**
 * 画像ファイルから width/height を取得する（ビルド時のみ。PHP の getimagesize 代替）
 * - ラスタ: image-size。SVG: ファイル読み + width/height/viewBox の正規表現パース。
 */
function getImageDimensions(absPath) {
  const ext = path.extname(absPath).toLowerCase();
  if (ext === ".svg") {
    try {
      const svg = fs.readFileSync(absPath, "utf8");
      let width = null;
      let height = null;
      const wMatch = svg.match(/\bwidth="([\d.]+)(px)?"/i);
      const hMatch = svg.match(/\bheight="([\d.]+)(px)?"/i);
      if (wMatch) width = Math.floor(Number(wMatch[1]));
      if (hMatch) height = Math.floor(Number(hMatch[1]));
      if ((!width || !height) && svg) {
        const vbMatch = svg.match(/\bviewBox="[\d.\-]+\s+[\d.\-]+\s+([\d.]+)\s+([\d.]+)"/i);
        if (vbMatch) {
          if (!width) width = Math.floor(Number(vbMatch[1]));
          if (!height) height = Math.floor(Number(vbMatch[2]));
        }
      }
      if (width != null && height != null) return { width, height };
    } catch (_) {}
    return null;
  }
  try {
    const result = imageSize(absPath);
    if (result && result.width != null && result.height != null) {
      return { width: result.width, height: result.height };
    }
  } catch (_) {}
  return null;
}

/**
 * テーマ画像をハッシュ付きアセットとしてdistに出力し、PHPが解決するためのマップを書き込む
 * - ソースキー: "src/assets/images/<relpath>"
 * - 出力値: { file, width?, height? }（寸法はビルド時に取得し、ランタイムの getimagesize を不要にする）
 */
function wpThemeImagesManifest() {
  const imagesRoot = path.resolve(__dirname, "src/assets/images");
  const allowExt = new Set([
    ".png",
    ".jpg",
    ".jpeg",
    ".gif",
    ".svg",
    ".webp",
    ".avif",
  ]);

  const idByKey = new Map();
  const dimsByKey = new Map();

  return {
    name: "wp-theme-images-manifest",
    apply: "build",
    buildStart() {
      const files = listFilesRecursive(imagesRoot).filter((abs) =>
        allowExt.has(path.extname(abs).toLowerCase())
      );

      for (const abs of files) {
        const rel = toPosixPath(path.relative(imagesRoot, abs));
        const key = `src/assets/images/${rel}`;
        const dims = getImageDimensions(abs);
        if (dims) dimsByKey.set(key, dims);
        const source = fs.readFileSync(abs);
        const fileId = this.emitFile({
          type: "asset",
          name: rel,
          source,
        });
        idByKey.set(key, fileId);
      }
    },
    generateBundle(_, bundle) {
      const map = {};
      for (const [key, id] of idByKey.entries()) {
        const file = toPosixPath(this.getFileName(id));
        const dims = dimsByKey.get(key);
        map[key] = {
          file,
          ...(dims && dims.width != null && dims.height != null
            ? { width: dims.width, height: dims.height }
            : {}),
        };
      }

      this.emitFile({
        type: "asset",
        fileName: "theme-assets.json",
        source: JSON.stringify(map, null, 2),
      });
    },
  };
}

/**
 * 環境変数をブール値として取得
 * "1", "true", "yes", "on" を true として扱う
 */
function envBool(name, defaultValue = false) {
  const v = process.env[name];
  if (v == null || v === "") return defaultValue;
  return ["1", "true", "yes", "on"].includes(String(v).toLowerCase());
}

/**
 * 環境変数を数値として取得
 * 無効な値の場合はデフォルト値を返す
 */
function envNumber(name, defaultValue) {
  const v = process.env[name];
  if (v == null || v === "") return defaultValue;
  const n = Number(v);
  return Number.isFinite(n) ? n : defaultValue;
}

/**
 * Vite設定
 */
export default defineConfig({
  /**
   * 基本設定
   * WordPress 側で参照する favicon 等はテーマ直下で管理するため、
   * Vite の publicDir コピーは使用しない（dist/ に複製されるのを避ける）
   */
  root: ".",
  base: "",
  publicDir: false,
  
  /**
   * 開発サーバー設定
   * HMR（Hot Module Replacement）対応のため、HTTPS設定とCORS設定を有効化
   */
  server: {
    host: true,
    port: 5173,
    strictPort: true,
    cors: true,
    headers: {
      "Access-Control-Allow-Origin": "*",
    },
    https: getHttpsConfig(),
    ...(getHttpsConfig()
      ? {
          hmr: { protocol: "wss" },
        }
      : {}),
    open: false
  },
  
  /**
   * ビルド設定
   * エントリーポイント（JS/CSS）の指定と、出力ファイル名のハッシュ付与設定
   */
  build: {
    outDir: path.resolve(__dirname, "dist"),
    emptyOutDir: true,
    // assetsInlineLimit: 0, // svgをインライン化させない場合はコメントアウト解除
    manifest: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, "src/assets/js/main.js"),
        style: resolve(__dirname, "src/assets/sass/style.scss"),
      },
      output: {
        assetFileNames: (info) => {
          const n = (info.name ?? "").replaceAll("\\", "/");
          const isImage = /\.(png|jpe?g|gif|svg|webp|avif)$/i.test(n);

          if (isImage) {
            // 可能な限りsrc/assets/images/**配下のサブディレクトリを維持する
            // 例: "demo/dummy1.jpg" -> "assets/images/demo/dummy1-[hash].jpg"
            const dir = path.posix.dirname(n);
            const ext = path.posix.extname(n);
            const base = path.posix.basename(n, ext);
            const subdir = dir === "." ? "" : `${dir}/`;
            return `assets/images/${subdir}${base}-[hash]${ext}`;
          }

          if (/\.css$/i.test(n)) return "assets/css/[name]-[hash][extname]";
          return "assets/[name]-[hash][extname]";
        },
        entryFileNames: "assets/js/[name]-[hash].js",
        chunkFileNames: "assets/js/[name]-[hash].js"
      }
    }
  },
  
  /**
   * プラグイン設定
   * WordPress用のカスタムプラグインと画像最適化プラグインを登録
   */
  plugins: [
    wpPhpFullReload(),
    wpThemeImagesManifest(),
    sassGlobImports(),
    // 画像圧縮とWebP変換
    viteImagemin({
      root: path.resolve(__dirname), // 絶対パスを維持（相対パスNG）
      onlyAssets: true,
      include: /\.(png|jpe?g|gif|svg)$/i,
      exclude: [/node_modules/],
      // exclude: [/node_modules/, /mv_bottom_bg\.png$/i], // mv_bottom_bg.pngは最適化しない場合は除外
      plugins: {
        // 静的インポートに変更
        jpg: imageminMozjpeg({ quality: envNumber("VITE_JPEG_QUALITY", 75), progressive: true }),
        png: imageminOptipng({ optimizationLevel: 2 }),
        gif: imageminGifsicle({ optimizationLevel: 2 }),
        svg: imageminSvgo()
      },
      ...(envBool("VITE_ENABLE_WEBP", false) // trueでwebP生成
        ? {
            makeWebp: {
              plugins: {
                // 静的インポートに変更
                jpg: imageminWebp({ quality: envNumber("VITE_WEBP_QUALITY", 75) }),
                png: imageminWebp({ quality: envNumber("VITE_WEBP_QUALITY", 75) }),
                gif: imageminGif2webp({ quality: envNumber("VITE_WEBP_QUALITY", 75) }),
              },
              formatFilePath: (file) =>
                file.replace(/\.(jpe?g|png|gif)$/i, ".webp"),
              // 'optimized' | number (bytes). このプロジェクトではデフォルトで'optimized'を使用
              skipIfLargerThan: envBool("VITE_WEBP_SKIP_IF_LARGER", true) ? "optimized" : 0,
            },
          }
        : {})
    })
  ]
});