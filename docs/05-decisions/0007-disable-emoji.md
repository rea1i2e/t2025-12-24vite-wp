# 7. 絵文字の無効化

**ステータス**: Accepted

## コンテキスト

WordPress は 4.2 以降、フロント・管理画面に絵文字用の検出スクリプトとスタイルをデフォルトで出力する。モダンブラウザでは絵文字をネイティブ表示するため、これらのアセットは不要であり、読み込み軽量化のため無効化したい。

WordPress 6.4 では `print_emoji_styles` が非推奨となり、フロントのスタイルは `wp_enqueue_scripts` にフックする `wp_enqueue_emoji_styles` でエンキューされるようになった。従来の `print_emoji_styles` の除去だけでは 6.4 以降でスタイルが残る可能性がある。

## 決定

**`functions-lib/func-base.php` の `ty_disable_emoji()` で、WordPress デフォルトの絵文字関連のスクリプト・スタイル・フィルタを一括で無効化する。**

- 除去対象: `print_emoji_detection_script`（wp_head / admin_print_scripts）、`print_emoji_styles`（wp_print_styles / admin_print_styles）、**`wp_enqueue_emoji_styles`（wp_enqueue_scripts）**、および `wp_staticize_emoji` / `wp_staticize_emoji_for_email` のフィルタ。
- 実行タイミング: `init` で `remove_action` / `remove_filter` を実行。
- 6.4 未満の環境では `wp_enqueue_emoji_styles` は登録されていないため、その `remove_action` は無害（何も行われない）。

## 結果

- **メリット**: 不要なインラインスクリプト・スタイルが読み込まれず、フロント・管理画面のパフォーマンスがわずかに向上する。
- **トレードオフ**: 古いブラウザや一部環境では、コンテンツ内の絵文字が □ や文字化けする可能性がある。本テンプレートは企業サイト等を想定し、コンテンツで絵文字を前提としない運用を想定しているため、この欠点は許容する。
- **保守**: WordPress のバージョンアップで絵文字まわりのフックや関数が変わった場合（例: 新フックの追加）は、本関数の除去対象を見直す必要がある。

## 関連ADR

なし
