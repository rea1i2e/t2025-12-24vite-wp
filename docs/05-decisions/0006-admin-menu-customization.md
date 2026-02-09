# 6. 管理画面メニューのカスタマイズ

**ステータス**: 承認済み

## コンテキスト

管理画面のメニューを整理し、不要な項目を非表示にしたり、よく編集する固定ページをトップレベルに常時表示したいニーズがありました。固定ページをメニューに出す場合、WordPress のメニューは投稿IDに依存するため、Local・テスト・本番でIDがずれると設定が環境ごとに変わる問題があります。

## 決定

**`functions-lib/func-admin-menu.php` で配列により管理画面メニューをカスタマイズする。**

- **非表示**: `$ty_admin_menu_remove_slugs` にメニュースラッグ（例: `edit.php`, `edit-comments.php`）を列挙し、`remove_menu_page()` で非表示にする。
- **トップレベル表示**: `$ty_admin_page_menu_slugs` に固定ページの**スラッグ**を列挙し、`get_page_by_path()` でページを取得して `add_menu_page()` でトップレベルメニューを追加。クリック時はその固定ページの編集画面へリダイレクトする。スラッグで指定するため、環境が変わっても同じ設定で動作する。

## 結果

- 管理メニューの「何を隠すか」「どの固定ページをトップに出すか」は `func-admin-menu.php` の2つの配列で一元管理する。
- `edit.php` を非表示にすると「投稿」メニュー全体が消えるため、`func-set-posttype-post.php` のラベル変更（投稿→お知らせ）は見た目に効かなくなる。お知らせメニューを残す場合は `$ty_admin_menu_remove_slugs` に `edit.php` を入れない。
- 詳細は [architecture.md](../01-development/architecture.md) の「MAY：管理画面メニュー」および機能ファイル一覧を参照。

## 関連ADR

なし
