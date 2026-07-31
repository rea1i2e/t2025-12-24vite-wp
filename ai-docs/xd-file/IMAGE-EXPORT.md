# XD 画像書き出し一覧（パス正本・雛形）

案件で XD / デザインから画像を置くときの**パス正本**。実装の `ty_img('…')` と揃えて追記する。

## 命名ルール

```text
src/assets/images/{page-slug}/{ブロック名}_{名前}.{ext}
```

- **page-slug** … ページ単位のディレクトリ。サイト共通は `common`
- **ブロック名** … BEM ブロックから `p-` / `c-` / `l-` を除いたもの（例: `p-top-mv` → `top-mv`）
- **名前** … **意味が分かる語を優先**（`logo` / `illust` / `thumb` / `icon` など）。`asset` のような汎用すぎる語は使わない。どうしても付かないときだけ `_01` `_02` の連番でよい
- **SP** … 末尾に `_sp`（例: `top/top-mv_illust_sp.png`）
- テンプレ同梱のデモ画像は使わない。未設置時は `ty_img` がパスを表示する（`.u-img-missing`）

## 書き出し一覧

| コード上のパス（`src/assets/images/` 以下） | 用途 | XD 手がかり |
|---|---|---|
| （例）`common/header_logo.svg` | ヘッダーロゴ | |
| （例）`top/top-mv_illust.png` | MV イラスト | |

新規セクションで画像を足したら、この表と実装のパスを同時に更新する。

## 寸法・圧縮（必須）

正本: ナレッジ `/Users/yoshiaki/working/2026-04-23kn/wiki/coding-ejs-html.md` §画像 / Skill `image-scale-check`・`image-size-check`。

| レイヤ | 方針 | 誰がやる |
|--------|------|----------|
| **解像度（約 2x）** | CSS 表示幅（PC デザイン幅）に対し `naturalWidth ÷ 表示幅 ≈ 1.8〜2.4`。`object-fit: cover` は**表示枠のアスペクトでクロップしてから** `表示×2` にリサイズ | **素材配置時**（人間 or エージェント） |
| **ファイル圧縮** | JPEG/PNG 等の再エンコード | **Vite ビルド**（`config/theme-build.config.js`）。配置時に余計な再圧縮をしない |
| **1MB 上限** | 原則 1 画像 1MB 以内（ロゴ・劣化不可イラストは例外可） | 提出前に `npm run check:images`（導入済みなら） |

### 約 2x の求め方

1. SCSS / XD から **PC の表示 CSS px** を取る。
2. 目標ピクセル = 表示 × **2**。
3. 元が目標より大きい → **縮小**して設置。小さい → **アップスケールしない**。
4. SVG は寸法の 2x 対象外。
