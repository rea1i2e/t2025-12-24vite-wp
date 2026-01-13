<?php

declare(strict_types=1);

/**
 * URL系ヘルパー
 * Viteのビルドが関与しないパスのみを扱う
 * ※Viteのビルドが関与するパスはfunc-vite.phpで扱う
 *
 * =========================================
 * 使い方（例）
 * =========================================
 *
 * ▼ テーマ直下の静的ファイルURL（favicon等）
 *   ty_theme('/favicon.ico');
 *   ty_theme('/apple-touch-icon.png');
 *
 * ▼ サイト内URL（出力）
 *   ty_page();        // トップ（末尾スラッシュ調整あり）
 *   ty_page('news');  // /news/（末尾スラッシュ調整あり）
 *
 * ▼ サイト内URL（文字列で取得）
 *   $url = ty_get_page('contact'); // /contact/
 *
 * ▼ スパム対策用メールアドレス保護用のHTMLを出力
 *   ty_email_protection('info', 'example.com'); // リンクあり
 *   ty_email_protection('info', 'example.com', false); // リンクなし（文字列）
 */

// テーマファイルURLを出力（テーマ直下からの相対パス）
function ty_theme(string $file = ''): void {
	echo esc_url(get_theme_file_uri($file));
}


// home_url を出力（スラッグ指定時は末尾スラッシュを正規化）
function ty_page(string $page = ''): void {
	// アンカー/クエリ/拡張子付きでなければ末尾に / を付与
	if (
		strpos($page, '#') === false &&
		strpos($page, '?') === false &&
		!preg_match('/\.[a-zA-Z0-9]+$/', $page)
	) {
		$page .= '/';
	}
	echo esc_url(home_url($page));
}

// home_url を返す（スラッグ指定時は末尾スラッシュを正規化）
function ty_get_page(string $page = ''): string {
	// アンカー/クエリ/拡張子付きでなければ末尾に / を付与
	if (
		strpos($page, '#') === false &&
		strpos($page, '?') === false &&
		!preg_match('/\.[a-zA-Z0-9]+$/', $page)
	) {
		$page .= '/';
	}
	return esc_url(home_url($page));
}

// メールアドレス保護用のHTMLを出力 
function ty_email_protection($user, $domain, $link = true) {
  $link_attr = $link ? 'true' : 'false';
  $email_display = $user . '[at]' . $domain;
  
  echo '<span class="js-email-protection" data-email-user="' . esc_attr($user) . '" data-email-domain="' . esc_attr($domain) . '" data-link="' . esc_attr($link_attr) . '"></span><noscript>' . esc_html($email_display) . '</noscript>';
}