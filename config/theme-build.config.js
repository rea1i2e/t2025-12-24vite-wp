/**
 * テーマの Vite ビルド設定（正本。静的テンプレ config/site.config.js の該当項目相当）
 *
 * 変更したら npm run build し直す。
 * PHP（prod）が参照するのは dist/theme-build-config.json（画像 format 用の imageAltFormats のみ）。
 *
 * WP テンプレ既定: 代替フォーマットは none（WebP/AVIF はプラグイン想定）。
 * 案件複製後は案件ごとに上書き（例: ik は imageAltFormats: "avif", useFileHash: false）。
 */
export const themeBuildConfig = {
  /** none | webp | avif | both */
  imageAltFormats: "none",
  /** dist 出力の JS/CSS/フォント/画像ファイル名に [hash] を付けるか */
  // useFileHash: true,
  useFileHash: false,
  /**
   * CSS を minify するか（vite build.cssMinify）。JS は常に minify される。
   */
  // cssMinify: true,
  cssMinify: false,
  /** JPEG 圧縮（mozjpeg） */
  jpegQuality: 75,
  /** WebP / AVIF の quality 目安（imageAltFormats が webp/avif/both のとき） */
  webpQuality: 75,
  /** true: 元より大きい代替フォーマットは出力しない */
  skipIfLargerThan: true,
};
