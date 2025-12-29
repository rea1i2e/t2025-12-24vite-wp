<?php

declare(strict_types=1);

/**
 * ナビメニューを一元管理
 *
 * =========================================
 * 使い方（例）
 * =========================================
 *
 * ▼ 単純にループしてリンクを出す（例: header/footer など）
 *   foreach (get_nav_items() as $item) {
 *     // $item['slug'], $item['text'] を使ってURL/表示名を組み立てる
 *   }
 *
 * ▼ 子階層（children）がある場合
 * - 'demo' のように $item['children'] を持つ要素があります
 * - 子メニューも出したい場合は再帰（または2段階ループ）で描画してください
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


