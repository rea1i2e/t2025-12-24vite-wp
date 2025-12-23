import { isExcluded } from "./utils.js";

/**
 * ページ設定
 */
const pages = {
  top: {
    label: "トップ",
    root: "./",
    path: "",
    title: "",
    description:
      "トップページの説明文です。サイトの概要や特徴を簡潔に記載してください。",
    keywords: "キーワード1,キーワード2,キーワード3",
  },
  demo: {
    label: "デモ一覧",
    root: "../",
    path: "demo/",
    title: "デモ一覧",
    description: "デモの一覧ページです。",
  },
  demoAccordion: {
    label: "アコーディオン",
    root: "../../",
    path: "demo/demo-accordion/",
    title: "アコーディオン",
    description: "アコーディオンデモページです。",
  },
  demoDialog: {
    label: "モーダル",
    root: "../../",
    path: "demo/demo-dialog/",
    title: "モーダル",
    description: "モーダルデモページです。",
  },
  demoSplide: {
    label: "スライダー（Splide）",
    root: "../../",
    path: "demo/demo-splide/",
    title: "フェードで切り替えるスライダー（Splide）",
    description: "フェードで切り替えるスライダー（Splide）デモページです。",
  },
  demoFadein: {
    label: "フェードイン",
    root: "../../",
    path: "demo/demo-fadein/",
    title: "フェードイン",
    description: "スクロールに応じて要素が順次表示されるデモページです。",
  },
  demoCssAnime: {
    label: "CSSアニメーション",
    root: "../../",
    path: "demo/demo-css-animation/",
    title: "CSSアニメーションデモ",
    description: "CSSアニメーションデモページです。",
  },
  demoGridLayout: {
    label: "グリッドレイアウト",
    root: "../../",
    path: "demo/demo-grid-layout/",
    title: "グリッドレイアウト",
    description: "グリッドレイアウトデモページです。",
  },
  demoHoverButton: {
    label: "ホバーエフェクト（ボタン）",
    root: "../../",
    path: "demo/demo-hover-button/",
    title: "ホバーエフェクト（ボタン）",
    description: "ホバーエフェクトのデモ（ボタン）ページです。",
  },
  demoHoverText: {
    label: "ホバーエフェクト（テキスト）",
    root: "../../",
    path: "demo/demo-hover-text/",
    title: "ホバーエフェクト（テキスト）",
    description: "ホバーエフェクトのデモ（テキスト）ページです。",
  },
  demoHoverCard: {
    label: "ホバーエフェクト（カード）",
    root: "../../",
    path: "demo/demo-hover-card/",
    title: "ホバーエフェクト（カード）",
    description: "ホバーエフェクトのデモ（カード）ページです。",
  },
  demoHoverChange: {
    label: "ホバーエフェクト（画像切り替え）",
    root: "../../",
    path: "demo/demo-hover-change/",
    title: "ホバーエフェクト（画像切り替え）",
    description: "ホバーエフェクト（画像切り替え）ページです。",
  },
  contact: {
    label: "お問い合わせ",
    root: "../",
    path: "contact/",
    title: "お問い合わせ",
    description:
      "お問い合わせフォームページです。お問い合わせを送信することができます。",
    keywords: "",
  },
  thanks: {
    label: "お問い合わせ完了",
    root: "../",
    path: "thanks/",
    title: "お問い合わせ完了",
    description:
      "お問い合わせフォームページです。お問い合わせを送信することができます。",
    keywords: "",
  },
  privacy: {
    label: "個人情報保護方針",
    root: "../",
    path: "privacy/",
    title: "個人情報保護方針",
    description: "個人情報保護方針ページです。",
    keywords: "",
  },
  // 外部リンク設置例
  x: {
    label: "X",
    root: "",
    path: "https://x.com/yoshiaki_12",
    targetBlank: true,
  },
};

export const siteConfig = {
  /**
   * 共通設定（案件によらず変更しないもの）
   */
  ejsPath: "./ejs/", // ルート相対パスで書くと、ズレる（システムルートの解釈）
  imagePath: "/assets/images/",

  /**
   * プロジェクト設定（案件固有のもの）
   */
  siteName: "静的サイト用ejsテンプレート",
  domain: "https://rea1i2e.net/",
  titleSeparator: " | ",

  /**
   * ページ除外設定
   * ヘッダー、ドロワー、フッターから除外するページの指定
   *
   * パターン指定方法:
   * - "demo*" → demoで始まるすべてのページを除外
   * - "demo[A-Z]*" → demoの後に大文字が続くページのみ除外（demoは除外しない）
   * - "contact" → contactのみ除外
   */
  headerExcludePages: ["demo[A-Z]*", "thanks"],
  drawerExcludePages: ["demo[A-Z]*", "thanks"],
  // footerExcludePages: ["demo[A-Z]*", "thanks"],

  // 除外ページチェック関数
  isExcluded,

  // ページ設定
  pages,
};

/**
 * 未定義の場合にデフォルト値を設定
 * コメントアウトしてもエラーを出さないため
 */
if (!siteConfig.headerExcludePages) {
  siteConfig.headerExcludePages = [];
}
if (!siteConfig.drawerExcludePages) {
  siteConfig.drawerExcludePages = [];
}
if (!siteConfig.footerExcludePages) {
  siteConfig.footerExcludePages = [];
}
