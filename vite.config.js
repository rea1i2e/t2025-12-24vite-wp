import { defineConfig } from "vite";
import { ViteEjsPlugin } from "vite-plugin-ejs";
import liveReload from "vite-plugin-live-reload";
import sassGlobImports from "vite-plugin-sass-glob-import";
import path, { resolve } from "node:path";
import { fileURLToPath } from "node:url";
import { globSync } from "glob";
import viteImagemin from "@vheemstra/vite-plugin-imagemin";
import imageminMozjpeg from "imagemin-mozjpeg";
import imageminPngquant from "imagemin-pngquant";
import imageminGifsicle from "imagemin-gifsicle";
import imageminSvgo from "imagemin-svgo";
import imageminWebp from "imagemin-webp";
// imagemin-gif2webp は CJS なので default import の互換に依存せず、名前空間受け取りにします
import gif2webpCjs from 'imagemin-gif2webp';
import { siteConfig } from "./config/site.config.js";
import { posts } from "./src/ejs/data/posts.js";
const imageminGif2webp = gif2webpCjs;


const __dirname = path.dirname(fileURLToPath(import.meta.url));

// src配下のHTMLを全部エントリに（publicディレクトリを除外）
const htmlFiles = globSync("src/**/*.html", {
  ignore: ["src/public/**/*.html"]
});

export default defineConfig({
  root: "src",
  base: "./",
  server: {
    host: true,
    open: true
  },
  build: {
    outDir: path.resolve(__dirname, "dist"),
    emptyOutDir: true,
    rollupOptions: {
      input: Object.fromEntries(
        htmlFiles.map((file) => [
          file.replace(/^src\//, "").replace(/\.html$/, ""),
          resolve(__dirname, file),
        ])
      ),
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
    ViteEjsPlugin({
      ...siteConfig,
      posts,
    }),
    liveReload(["ejs/**/*.ejs"]),
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