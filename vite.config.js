import { defineConfig } from "vite";
import sassGlobImports from "vite-plugin-sass-glob-import";
import fs from "node:fs";
import path, { resolve } from "node:path";
import { fileURLToPath } from "node:url";
import viteImagemin from "@vheemstra/vite-plugin-imagemin";
import imageminMozjpeg from "imagemin-mozjpeg";
import imageminPngquant from "imagemin-pngquant";
import imageminGifsicle from "imagemin-gifsicle";
import imageminSvgo from "imagemin-svgo";
import imageminWebp from "imagemin-webp";
// imagemin-gif2webp は CJS なので default import の互換に依存せず、名前空間受け取りにします
import gif2webpCjs from 'imagemin-gif2webp';
const imageminGif2webp = gif2webpCjs;


const __dirname = path.dirname(fileURLToPath(import.meta.url));
const certDir = path.resolve(__dirname, ".certs");

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
 * Full reload the browser when PHP templates change (WordPress).
 * Note: PHP cannot be hot-replaced; we trigger a browser reload instead.
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

function toPosixPath(p) {
  return p.replaceAll("\\", "/");
}

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
 * Emit theme images to dist as hashed assets, and write a map for PHP to resolve.
 * - source key: "src/assets/images/<relpath>"
 * - output value: "<dist-relative-path>" (e.g. "assets/images/foo-xxxx.jpg")
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
        const source = fs.readFileSync(abs);
        const fileId = this.emitFile({
          type: "asset",
          // Keep original subdirectory info in the name so output can preserve dirs.
          name: rel,
          source,
        });
        idByKey.set(key, fileId);
      }
    },
    generateBundle(_, bundle) {
      const map = {};
      for (const [key, id] of idByKey.entries()) {
        map[key] = toPosixPath(this.getFileName(id));
      }

      // Write a simple JSON map for PHP to resolve <img src> in production.
      // (Avoid writing under ".vite/" to reduce conflicts with Vite's manifest plugin.)
      this.emitFile({
        type: "asset",
        fileName: "theme-assets.json",
        source: JSON.stringify(map, null, 2),
      });
    },
  };
}

function envBool(name, defaultValue = false) {
  const v = process.env[name];
  if (v == null || v === "") return defaultValue;
  return ["1", "true", "yes", "on"].includes(String(v).toLowerCase());
}

function envNumber(name, defaultValue) {
  const v = process.env[name];
  if (v == null || v === "") return defaultValue;
  const n = Number(v);
  return Number.isFinite(n) ? n : defaultValue;
}

export default defineConfig({
  root: ".",
  base: "",
  // WordPress 側で参照する favicon 等はテーマ直下で管理するため、
  // Vite の publicDir コピーは使用しない（dist/ に複製されるのを避ける）
  publicDir: false,
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
  build: {
    outDir: path.resolve(__dirname, "dist"),
    emptyOutDir: true,
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
            // Preserve subdirectories under src/assets/images/** when possible.
            // Example: "demo/dummy1.jpg" -> "assets/images/demo/dummy1-[hash].jpg"
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
  plugins: [
    wpPhpFullReload(),
    wpThemeImagesManifest(),
    sassGlobImports(),
    // 画像圧縮とWebP変換
    viteImagemin({
      root: path.resolve(__dirname), // 絶対パスを維持（相対パスNG）
      onlyAssets: true,
      include: /\.(png|jpe?g|gif|svg)$/i,
      plugins: {
        // 静的インポートに変更
        jpg: imageminMozjpeg({ quality: envNumber("VITE_JPEG_QUALITY", 75), progressive: true }),
        png: imageminPngquant({ quality: [0.65, 0.8], speed: 3 }),
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
              // 'optimized' | number (bytes). This project uses 'optimized' by default.
              skipIfLargerThan: envBool("VITE_WEBP_SKIP_IF_LARGER", true) ? "optimized" : 0,
            },
          }
        : {})
    })
  ]
});