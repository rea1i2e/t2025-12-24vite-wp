<?php

declare(strict_types=1);

/**
 * YouTube oEmbed の埋め込みコードを修正
 *
 * WordPressのoEmbed機能でYouTube動画を埋め込む際に、
 * `rel=0` パラメータを追加して関連動画を非表示にする
 *
 * 注意: この関数はWordPressのoEmbed機能（URLを貼り付けて自動変換される場合）にのみ適用されます。
 * PHPテンプレートに直接 `<iframe>` タグを書いた場合は適用されません。
 * その場合は、iframeのsrc属性に直接 `&rel=0` を追加してください。
 *
 * @param string $html oEmbedで生成されたHTML
 * @param string $url 埋め込み対象のURL
 * @param array $args oEmbedの引数
 * @return string 修正後のHTML
 */
function ty_modify_youtube_oembed(string $html, string $url, array $args): string {
	if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
		$html = str_replace('?feature=oembed', '?feature=oembed&rel=0', $html);
	}
	return $html;
}
add_filter('oembed_result', 'ty_modify_youtube_oembed', 10, 3);