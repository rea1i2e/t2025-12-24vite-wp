# 固定ページ一括インポート（pages.json → WordPress）

`pages.json` を読み込み、固定ページ（`post_type=page`）を一括で作成/更新します。
実行は WP-CLI 経由で行います。

## 実行環境について（重要）

このツールは **Local（Local.app）の Site Shell / ターミナルで実行する前提**です。
（Cursor内ターミナル等だと `wp` コマンドがPATHに無く、`command not found: wp` になることがあります。）

## 運用方針（重要）

- **Local でページを作成・更新**します
- **テスト環境 / 本番環境へは、このツールで直接投入せず**、WordPressの「エクスポート / インポート（XML）」で移行します

## 前提

- WordPress が動作している環境であること
- WP-CLI（`wp` コマンド）が使えること（Local の Site Shell で `wp --info` が通ること）

## 手順

### 1) `pages.json` の用意

`pages.json` は固定ページの定義です。最低限 `title` と `slug` が必要です。

- `title`（必須）: 管理画面に表示されるタイトル
- `slug`（必須）: スラッグ（子ページの場合も基本は単体スラッグでOK）
- `parent_slug`（任意）: 親ページのスラッグ（親がある場合）
- `status`（任意）: `draft` / `publish` など（未指定は `publish`）
- `content`（任意）: 本文（空でも可）

> 補足: 互換のため `parent` キーも受け付けますが、基本は `parent_slug` を使ってください。

### 重要：引数はフラグではなく「位置引数」

環境によっては `wp eval-file` が未知のオプション（例: `--mode` / `--dry-run`）を弾くため、
このツールはフラグではなく **位置引数**で受け取ります。

- 1) JSONパス（必須）
- 2) mode（任意: create|upsert|warn|skip / 既定: upsert）
- 3) dry_run（任意: 1ならDB変更なし / 既定: 0）

### 2) 確認のコマンド（dry-run / DB変更なし）

WordPress のインストールディレクトリ（`wp-config.php` がある場所）で実行します。

```bash
wp eval-file wp-content/themes/t2025-12-24vite-wp/tools/import-pages.php wp-content/themes/t2025-12-24vite-wp/tools/pages.json upsert 1
```

#### 結果の確認方法（ログの見方）

- `[dry-run] Create: ...`：本番実行すると **新規作成**される予定
- `[dry-run] Update: ...`：本番実行すると **更新**される予定
- `parent not found ... parent=0`：親が見つからず **階層が崩れる可能性**
- `slug/title missing`：データ不備で **取り込みスキップ**
- 最後に `created=... updated=... skipped=... mode=... dry-run=...` の集計が出ます

### 3) 本番実行（DB変更あり）

dry-run で問題がなければ `dry_run` を省略（または `0`）して実行します。

```bash
wp eval-file wp-content/themes/t2025-12-24vite-wp/tools/import-pages.php wp-content/themes/t2025-12-24vite-wp/tools/pages.json upsert
```

## `.sh` で実行したい場合（任意）

`import-pages.sh` は中で `wp eval-file ...` を叩くだけのラッパーです。

### 通常実行

```bash
bash wp-content/themes/t2025-12-24vite-wp/tools/import-pages.sh
```

### dry-run

```bash
DRY_RUN=1 bash wp-content/themes/t2025-12-24vite-wp/tools/import-pages.sh
```

