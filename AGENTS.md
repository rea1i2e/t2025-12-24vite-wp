# AIエージェント向けプロジェクト理解ガイド

このドキュメントは、AIエージェント（Cursor / ChatGPT）がこのプロジェクトを理解し、適切なコード変更や提案を行うためのガイドです。

**人間が読む場合は、[docs/01-development/README.md](docs/01-development/README.md) を参照してください。**

---

## 技術スタック

- **WordPress**: クラシックテーマ（PHPテンプレート）
- **Vite**: フロントエンドビルドツール（HMR対応）
- **Sass**: CSSプリプロセッサ
- **PostCSS**: CSS後処理（autoprefixer、メディアクエリソート）

---

## コーディングルール

### 汎用ルール（WordPress・静的コーディング共通）

ナレッジリポジトリ `/Users/yoshiaki/working/2026-03-20kn/` の `coding-rules/` を参照すること。

| ファイル | 内容 |
|---|---|
| [`coding-rules/common.md`](/Users/yoshiaki/working/2026-03-20kn/coding-rules/common.md) | 実装方針・設計・判断基準 |
| [`coding-rules/php.md`](/Users/yoshiaki/working/2026-03-20kn/coding-rules/php.md) | PHP |
| [`coding-rules/ejs-html.md`](/Users/yoshiaki/working/2026-03-20kn/coding-rules/ejs-html.md) | HTML構造・クラス命名・画像実装 |
| [`coding-rules/sass.md`](/Users/yoshiaki/working/2026-03-20kn/coding-rules/sass.md) | Sass/SCSS |
| [`coding-rules/javascript.md`](/Users/yoshiaki/working/2026-03-20kn/coding-rules/javascript.md) | JavaScript |

### このテンプレート固有のルール

- **[docs/01-development/coding-standards.md](docs/01-development/coding-standards.md)**: テンプレート固有のコーディング規約（`ty_` プレフィックス・`kiso.css` 前提・プロパティ指定順など）
- **[docs/01-development/architecture.md](docs/01-development/architecture.md)**: 設計判断・MUST/SHOULD/MAY

---

## アーキテクチャ概要

詳細は [docs/01-development/architecture.md](docs/01-development/architecture.md) を参照してください。

### 開発環境（dev）のフロー

```
ブラウザ → WordPressページリクエスト
    ↓
WP_Theme_PHP（header.php, functions.php など）
    ↓
load_assets（アセット読み込み指示）
    ↓
Vite_dev_server(HMR) ← localhost:5173 から CSS/JS を取得
```

### 本番環境（prod）のフロー

```
npm run build
    ↓
vite build
    ↓
dist/ にハッシュ付きアセットを出力
    ├─ assets/js/main-*.js
    ├─ assets/css/style-*.css
    └─ .vite/manifest.json（パスマッピング）
    ↓
WP_Theme_PHP
    ↓
read_manifest（dist/.vite/manifest.json を読み込み）
    ↓
dist/ から実際のファイルを enqueue
```

---

## Git（エージェント向け）

- **`commit` / `push` / ブランチ操作 / マージ / `stash` など、リポジトリに履歴やリモートへ影響する Git 操作は、ユーザーから明示的に依頼があった場合のみ行う。**
- 作業完了・ドキュメント更新・整理を理由に、依頼なく自動でコミットしない。
- コミットが必要そうなときは、変更内容を説明したうえでユーザーにコミット・push を依頼するか確認する。
- コミットメッセージは日本語でよい（`.cursorrules` 参照）。

---

## 変更時の注意点

- **Vite連携の仕組みを変更しない**: dev/prod判定とmanifest読み込みの仕組みは必須
- **関数名のプレフィックス**: `ty_` を必ず付与（WordPress/プラグインとの衝突回避）
- **画像パスの解決**: CSS内はViteが解決、HTML内は `ty_theme_image_url()` を使用

---

## ドキュメント化の基準

変更時に「ドキュメントを残すか」を一貫して判断するための基準です。軽微な修正はドキュメントを増やさず、再発防止・合意形成・保守に効く変更だけ記録を残します。

### ドキュメントを残さなくてよい変更（例）

- 余白・フォントサイズ・色など、単発のスタイル調整
- 既存テンプレート内の文言・ラベルの修正
- typo修正、コメントの追記・削除
- 既存ルールに沿った単純なマークアップ追加

### ドキュメントを残すべき変更（例）

- `functions.php` / `functions-lib`: 新規 `require`、フック追加・削除、enqueue の変更
- アセット読み込み: エントリ追加、Vite以外の読み込み方法の導入
- Vite設定: `vite.config.*` の変更
- ルール・規約の変更
- 依存の追加・削除・バージョン方針

### 残す場合の行き先ガイド

| 内容 | 行き先 |
|------|--------|
| なぜこの判断をしたか（技術判断・トレードオフ） | [docs/05-decisions/](docs/05-decisions/)（ADR） |
| 案件固有の仕様・制約 | [docs/04-project-specific/overview.md](docs/04-project-specific/overview.md) |
| 設計・アーキテクチャのルール | [docs/01-development/architecture.md](docs/01-development/architecture.md) |
| ハマりどころ・対処方法 | [docs/03-troubleshooting/troubleshooting.md](docs/03-troubleshooting/troubleshooting.md) |
| 移行・環境差分・履歴 | [docs/06-reference/migration-log.md](docs/06-reference/migration-log.md) |

---

## 参考ドキュメント

- **設計判断・守るルール**: [docs/01-development/architecture.md](docs/01-development/architecture.md)
- **テンプレート固有のコーディング規約**: [docs/01-development/coding-standards.md](docs/01-development/coding-standards.md)
- **開発ガイド**: [docs/01-development/development.md](docs/01-development/development.md)
