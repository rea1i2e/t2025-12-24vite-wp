<?php

declare(strict_types=1);

/**
 * マップ埋め込みなど iframe の HTML 断片用、`wp_kses()` 第2引数の許可リスト
 *
 * 使用例:
 * echo wp_kses( COMPANY_MAP, ty_map_iframe_wp_kses_allowed() );
 *
 * 子テーマやプラグインから属性を足す場合はフィルタ `ty_map_iframe_wp_kses_allowed` を使う。
 *
 * ---
 * メモ: テーマから HTML に値を出すときのエスケープ（出す場所で関数を選ぶ）
 *
 * | 出す場所・用途 | 関数 |
 * |----------------|------|
 * | 要素の中身（プレーンテキスト） | esc_html() |
 * | 属性値（title / data-* / 動的な class 名など） | esc_attr() |
 * | href / src など URL として解釈される値 | esc_url() |
 * | 投稿本文と同等のタグセットで HTML を許可 | wp_kses_post() |
 * | 許可タグを独自に絞る HTML 断片（本ファイルの iframe など） | wp_kses( $html, $allowed_html ) |
 *
 * - wp_kses / wp_kses_post で出す HTML はすでにサニタイズ済み。**その外側に esc_html() を重ねない**（表示が壊れる）。
 * - 定数の URL をリンクにする: `echo esc_url( SNS_X_URL );` — テキストラベルは `esc_html()`。
 *
 * @return array<string, array<string, bool>>
 */
function ty_map_iframe_wp_kses_allowed(): array
{
	$allowed = [
		'iframe' => [
			'src' => true,
			'title' => true,
			'width' => true,
			'height' => true,
			'style' => true,
			'class' => true,
			'allow' => true,
			'allowfullscreen' => true,
			'loading' => true,
			'referrerpolicy' => true,
			'frameborder' => true,
		],
	];

	/**
	 * iframe 埋め込み用 wp_kses 許可リスト
	 *
	 * @param array<string, array<string, bool>> $allowed
	 */
	return (array) apply_filters('ty_map_iframe_wp_kses_allowed', $allowed);
}
