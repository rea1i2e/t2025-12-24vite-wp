<?php

declare(strict_types=1);

/**
 * ナビメニューを一元管理
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
			'slug' => 'demo',
			'text' => 'デモ一覧',
			'children' => [
				[
					'slug' => 'demo-splide',
					'text' => 'スライダー（Splide）',
				],
				[
					'slug' => 'demo-dialog',
					'text' => 'モーダル（dialog）',
				],
				[
					'slug' => 'demo-tab',
					'text' => 'タブ切り替え（tab）',
				],
				[
					'slug' => 'demo-accordion',
					'text' => 'アコーディオン（accordion）',
				]
			]
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


