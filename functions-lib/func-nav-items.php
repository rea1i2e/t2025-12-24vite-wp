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
 *   foreach (ty_get_nav_items() as $item) {
 *     // $item['slug'], $item['text'] を使ってURL/表示名を組み立てる
 *     // 表示用データは ty_get_nav_item_data($item, '接頭辞') で取得（url, current_class, target_attr, data_section_id_attr, modifier_class）
 *   }
 *
 * ▼ 子階層（children）がある場合
 * - 'demo' のように $item['children'] を持つ要素があります
 * - 子メニューも出したい場合は再帰（または2段階ループ）で描画してください
 *
 * =========================================
 * 項目のキー説明
 * =========================================
 *
 * - slug（必須）: サイト内の場合はページスラッグ。http:// または https:// で始まる場合は外部URLとして扱う。
 * - text（必須）: 表示ラベル。
 * - section_id（任意）: スクロール連動カレント用。.js-section の id と一致させる。省略時は data-section-id を出さない。
 * - modifier（任意）: li 等に付与する修飾クラス用（例: 'contact' → p-footer__nav-item--contact）。
 * - target（任意）: リンクの target 属性。'_blank' の場合は rel="noopener noreferrer" も付与される。
 * - children（任意）: 子メニュー項目の配列。各要素も slug, text 必須。
 *
 * ▼ 記述例
 *   ['slug' => 'top', 'text' => 'トップ']
 *   ['slug' => 'news', 'text' => 'お知らせ']
 *   ['slug' => 'about', 'text' => '会社概要', 'section_id' => 'intro']
 *   ['slug' => 'https://example.com', 'text' => '外部サイト', 'target' => '_blank']
 *   ['slug' => 'contact', 'text' => 'お問い合わせ', 'modifier' => 'contact']
 *   ['slug' => 'parent', 'text' => '親', 'children' => [['slug' => 'child', 'text' => '子', 'section_id' => 'child']]]
 *
 * ▼ 除外する場合の記述例（ヘッダー等で一部項目を出さないとき）
 *   foreach (ty_get_nav_items() as $item) {
 *     if (in_array($item['slug'], ['privacy-policy', 'terms-of-use'])) continue;
 *     $item_data = ty_get_nav_item_data($item, 'p-header__pc-nav-item');
 *     // $item_data['url'], $item_data['current_class'], $item_data['target_attr'], $item_data['data_section_id_attr'], $item_data['modifier_class']
 *   }
 *
 * @return array<int, array{slug:string,text:string,section_id?:string,modifier?:string,target?:string,children?:array}>
 */

function ty_get_nav_items(): array {
	return [
			[
				'slug' => 'top',
				'text' => 'トップ',
			],
			[
				'slug' => 'about',
				'text' => '会社概要',
			],
			[
				'slug' => 'about/#access',
				'text' => 'アクセス（会社概要）',
				'section_id' => 'access',
			],
		[
			'slug' => 'news',
			'text' => 'お知らせ',
		],
		[
			'slug' => 'works',
			'text' => '制作実績',
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
					'slug' => 'top/#demo-dialog',
					'text' => 'モーダル（dialog）',
					'section_id' => 'demo-dialog',
				],
				[
					'slug' => 'top/#demo-tab',
					'text' => 'タブ切り替え（tab）',
					'section_id' => 'demo-tab',
				],
				[
					'slug' => 'demo-accordion',
					'text' => 'アコーディオン（accordion）',
				],
				[
					'slug' => 'top/#demo-image',
					'text' => 'imgタグの出力',
					'section_id' => 'demo-image',
				],
			]
		],
		[
			'slug' => 'https://x.com/yoshiaki_12',
			'text' => 'X（外部リンク）',
			'target' => '_blank',
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

/**
 * ナビ項目の slug からリンクURLを取得する。
 * http:// または https:// で始まる場合はそのまま esc_url して返す。
 * それ以外は ty_get_page() でサイト内URLを返す（'top' または空のときはトップ）。
 */
function ty_get_nav_item_url(string $slug): string {
	if (str_starts_with($slug, 'http://') || str_starts_with($slug, 'https://')) {
		return esc_url($slug);
	}
	return ($slug === 'top' || $slug === '') ? ty_get_page() : ty_get_page($slug);
}

/**
 * ナビ項目の slug から現在ページ用クラス名を取得する。
 * 現在表示中のページと一致する場合は 'is-current'、それ以外は '' を返す。
 */
function ty_get_nav_item_current_class(string $slug): string {
	if (is_front_page() && $slug === 'top') {
		return 'is-current';
	}
	// 投稿ページ（表示設定で「投稿ページ」に指定した固定ページ）表示時はメインクエリが投稿一覧になるため is_page() が false になる
	if (is_home()) {
		$posts_page_id = (int) get_option('page_for_posts');
		if ($posts_page_id > 0) {
			$posts_page = get_post($posts_page_id);
			if ($posts_page && isset($posts_page->post_name) && $posts_page->post_name === $slug) {
				return 'is-current';
			}
		}
	}
	if (is_page($slug) || is_post_type_archive($slug) || is_category($slug) || is_tax($slug)) {
		return 'is-current';
	}
	return '';
}

/**
 * ナビ項目の target 値からリンク用 target 属性文字列を取得する。
 * 空の場合は ''。'_blank' の場合は rel="noopener noreferrer" も含む。
 */
function ty_get_nav_item_target_attr(string $target = ''): string {
	if ($target === '') {
		return '';
	}
	$attr = ' target="' . esc_attr($target) . '"';
	if ($target === '_blank') {
		$attr .= ' rel="noopener noreferrer"';
	}
	return $attr;
}

/**
 * ナビ項目の表示用データ（URL・カレントクラス・属性など）をまとめて取得する。
 * @param array{slug: string, target?: string, section_id?: string, modifier?: string} $item ナビ項目（slug 必須）
 * @param string $modifier_prefix 修飾クラス用の接頭辞（例: 'p-header__pc-nav-item'）。空のときは modifier_class は ''。
 * @return array{url: string, current_class: string, target_attr: string, data_section_id_attr: string, modifier_class: string}
 */
function ty_get_nav_item_data(array $item, string $modifier_prefix = ''): array {
	$slug = (string) ($item['slug'] ?? '');
	$target = (string) ($item['target'] ?? '');
	$section_id = (string) ($item['section_id'] ?? '');
	$modifier = (string) ($item['modifier'] ?? '');

	$data_section_id_attr = $section_id !== '' ? ' data-section-id="' . esc_attr($section_id) . '"' : '';
	$modifier_class = ($modifier_prefix !== '' && $modifier !== '') ? ' ' . $modifier_prefix . '--' . $modifier : '';

	return [
		'url' => ty_get_nav_item_url($slug),
		'current_class' => ty_get_nav_item_current_class($slug),
		'target_attr' => ty_get_nav_item_target_attr($target),
		'data_section_id_attr' => $data_section_id_attr,
		'modifier_class' => $modifier_class,
	];
}

