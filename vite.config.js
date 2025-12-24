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

export default defineConfig({
  root: ".",
  base: "",
  publicDir: "src/public",
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
          const n = info.name ?? "";
          if (/\.(png|jpe?g|gif|svg|webp|avif)$/i.test(n))
            return "assets/images/[name]-[hash][extname]";
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
    sassGlobImports(),
    // 画像圧縮とWebP変換
    viteImagemin({
      root: path.resolve(__dirname), // 絶対パスを維持（相対パスNG）
      onlyAssets: true,
      include: /\.(png|jpe?g|gif|svg)$/i,
      plugins: {
        // 静的インポートに変更
        jpg: imageminMozjpeg({ quality: 75, progressive: true }),
        png: imageminPngquant({ quality: [0.65, 0.8], speed: 3 }),
        gif: imageminGifsicle({ optimizationLevel: 2 }),
        svg: imageminSvgo()
      },
      makeWebp: {
        plugins: {
          // 静的インポートに変更
          jpg: imageminWebp({ quality: 75 }),
          png: imageminWebp({ quality: 75 }),
          gif: imageminGif2webp({ quality: 75 }),
        },
        formatFilePath: (file) => file.replace(/\.(jpe?g|png|gif)$/i, ".webp"),
        skipIfLargerThan: "optimized"
      }
    })
  ]
});