<?php

declare(strict_types=1);

/**
 * - テーマ機能を `functions-lib/` 配下へ分割して、globで読み込む
 * - 依存関係がある場合だけ、読み込み順を明示できるようにする
 *
 * `$ordered` に入れるべきファイルの目安（＝順序指定が必要なケース）:
 * - ファイル読み込み時点（トップレベル）で、別ファイルの関数/定数/クラスを参照・実行する
 * - 別ファイルで定義される関数/定数を前提に、定数定義や初期化処理を行う
 * - 「先に定義されていないと致命的エラーになる」依存がある（`function_exists` 等で逃がしていない）
 *
 * 逆に、WordPress の action/filter のコールバック内で参照するだけなら、
 * ほとんどの場合は順序指定が不要（リクエスト処理時点では全ファイルが読み込まれているため）です。
 *
 * 補足:
 * - `functions-lib/` に `.php` を追加すると、`_` 始まりを除き自動で読み込まれます。
 */

$baseDir = get_theme_file_path('functions-lib');

// 依存関係があるものだけ、ここに列挙して先に読み込む（通常は空でOK）。
$ordered = [
	'func-vite.php',
];

// 読み込み順を指定したファイルの読み込み
foreach ($ordered as $rel) {
	$path = $baseDir . '/' . $rel;
	if (file_exists($path)) {
		require_once $path;
	}
}

// 残りを自動ロード（順序はファイル名ソートで決定的にする）
$files = glob($baseDir . '/*.php');
if ($files === false) {
	$files = [];
}

// 環境（ファイルシステム/OS/実装差）によって並び順が保証されないことがあるため、sort() で ファイル名の昇順に揃えて「読み込み順を決定的（毎回同じ）」にする
sort($files, SORT_STRING);

foreach ($files as $file) {
	$name = basename($file);
	if ($name === 'loader.php') continue;
	if (str_starts_with($name, '_')) continue;
	if (in_array($name, $ordered, true)) continue;
	require_once $file;
}


