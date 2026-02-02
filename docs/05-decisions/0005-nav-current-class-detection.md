# 5. ナビゲーションのカレント判定方法

**ステータス**: 承認済み

## コンテキスト

ナビゲーション項目に「現在のページ」を示す `is-current` クラスを付与する必要があり、その判定をサーバー側（PHP）で行うか、クライアント側（JavaScript）で行うかを検討しました。

**検討した2つのアプローチ：**

1. **PHP（サーバー側）**: `ty_get_nav_item_current_class($slug)` のように、WordPress の条件タグ（`is_front_page()`, `is_page()`, `is_home()` 等）で判定し、初回 HTML に `is-current` を出力する。
2. **JavaScript（クライアント側）**: DOMContentLoaded でナビリンクを取得し、`window.location.pathname` と各リンクの `href` の pathname を比較して一致した要素に `is-current` を付与する。

## 決定

**PHP（サーバー側）での判定を採用する。**

実装は `functions-lib/func-nav-items.php` の `ty_get_nav_item_current_class()` に集約し、`ty_get_nav_item_data($item)` 経由で `current_class` を返す現状の方式を維持する。

## 理由

- **SEO・アクセシビリティ**: 初回 HTML に正しい状態が含まれるため、クローラーや支援技術が「今どのページか」を把握しやすい。
- **フラッシュ防止**: 読み込み直後から正しいクラスが付いているため、JS 実行後にクラスが付くことによる一瞬のチラつきがない。
- **WordPress の文脈を正しく扱える**: 固定ページ・投稿ページ（表示設定の「投稿ページ」）・カスタム投稿タイプ・カテゴリ・タクソノミーなど、WordPress の条件に合わせた正確な判定ができる。投稿ページ表示時は `is_page()` が false になるため、`is_home()` と `page_for_posts` のスラッグ比較で対応している。
- **JavaScript が必須でない**: ナビのカレント表示に JS が不要で、スクリプト無効時も正しく表示される。
- **既存実装との一貫性**: ナビの URL・target 属性と同様に、PHP でリンク用データをまとめて返す設計と揃う。

JavaScript 方式は「PHP に依存しない」「静的サイトにも流用しやすい」という利点はあるが、本テーマは WordPress を前提とし、投稿ページでの `is-current` を正確に付けたい要件があるため、サーバー側判定のメリットが大きいと判断した。

## 結果

- ナビのカレント判定は `ty_get_nav_item_current_class(string $slug)` で行う。
- テンプレート（header.php / footer.php）では `ty_get_nav_item_data($item)` の `current_class` を利用する。
- カレント判定の変更・拡張は `func-nav-items.php` 内に集約する。

## 関連ADR

- [0002-nav-item-url-helper.md](0002-nav-item-url-helper.md) — ナビ項目の URL 取得方法（`ty_get_page()` を直接使用する方針）。本 ADR はカレント判定に限定し、リンク用データは `ty_get_nav_item_data()` でまとめて取得する現行設計を前提としている。
