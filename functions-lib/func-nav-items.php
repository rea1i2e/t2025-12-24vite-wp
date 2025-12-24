<?php

declare(strict_types=1);

/**
 * ナビメニューを一元管理（旧テーマ互換）
 *
 * @return array<int, array{slug:string,text:string,modifier?:string}>
 */
function get_nav_items(): array {
	return [
		[
			'slug' => 'top',
			'text' => 'トップ',
		],
		[
			'slug' => 'news',
			'text' => 'お知らせ',
		],
		[
			'slug' => 'works',
			'text' => '実績',
		],
		[
			'slug' => 'contact',
			'text' => 'お問い合わせ',
			'modifier' => 'contact',
		],
		[
			'slug' => 'privacy-policy',
			'text' => 'プライバシーポリシー',
		],
		[
			'slug' => 'terms-of-use',
			'text' => '利用規約',
		],
	];
}


