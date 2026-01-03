<?php

declare(strict_types=1);

/**
 * 投稿が指定した日数以内であるか判定
 *
 * 投稿の公開日時から現在までの経過日数を計算し、
 * 指定した日数以内であれば `true` を返す
 *
 * =========================================
 * 使い方（例）
 * =========================================
 *
 * ▼ デフォルト（7日以内）
 *   $modifier = ty_new_post() ? 'p-post-items--new' : '';
 *
 * ▼ カスタム日数（3日以内）
 *   $modifier = ty_new_post(3) ? 'p-post-items--new' : '';
 *
 * ▼ カスタム日時を指定
 *   $modifier = ty_new_post(7, strtotime('2024-01-01')) ? 'p-post-items--new' : '';
 *
 * @param int $days 判定する日数（デフォルト: 7日）
 * @param int|null $entry_time 投稿日時のUnixタイムスタンプ（nullの場合は現在の投稿の日時を使用）
 * @return bool 指定日数以内であれば `true`、それ以外は `false`
 */
function ty_new_post(int $days = 7, ?int $entry_time = null): bool {
	$today = (int) date_i18n('U');
	$entry = $entry_time ?? (int) get_the_time('U');
	$post = ($today - $entry) / 86400;
	return $days > $post;
}

