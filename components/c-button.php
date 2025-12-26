<?php
/**
 * ボタンコンポーネント
 *
 * =========================================
 * 使い方（例）
 * =========================================
 *
 * ▼ リンクとして出力（追加属性あり）
 *   get_template_part('components/c-button', null, [
 *     'text' => '...', // ※表示テキストは呼び出し側で指定
 *     'href' => '...',
 *     'attributes' => [
 *       'data-slidein-mask' => 'white',
 *       'aria-label' => '...',
 *     ],
 *   ]);
 *
 * ▼ target="_blank"（rel は自動で noopener noreferrer）
 *   get_template_part('components/c-button', null, [
 *     'text' => '...',
 *     'href' => '...',
 *     'target' => '_blank',
 *   ]);
 *
 * ▼ href なし（<span> として出力）
 *   get_template_part('components/c-button', null, [
 *     'text' => '...',
 *   ]);
 *
 * @param array $args 引数配列
 *   - text (string, 必須): ボタンのテキスト
 *   - href (string, オプション): リンクURL（指定がない場合は<span>タグ）
 *   - target (string, オプション): ターゲット属性（例: '_blank'）
 *   - attributes (array, オプション): 追加属性の配列（例: ['data-slidein-mask' => 'white', 'aria-label' => '...']）
 */

declare(strict_types=1);

$args = isset($args) && is_array($args) ? $args : [];

$text = isset($args['text']) ? (string) $args['text'] : '';
$href = isset($args['href']) ? (string) $args['href'] : '';
$target = isset($args['target']) ? (string) $args['target'] : '';
$extra_attributes = (isset($args['attributes']) && is_array($args['attributes'])) ? $args['attributes'] : [];

/**
 * 追加属性（任意属性）を文字列に変換する
 *
 * @param array<string, mixed> $attrs
 */
$build_extra_attrs_string = static function (array $attrs): string {
	$out = '';

	foreach ($attrs as $key => $value) {
		if (!is_string($key) || $key === '') continue;

		// 属性名として使える範囲に寄せる（英数字/ハイフン/アンダースコアのみ）
		$key = preg_replace('/[^a-zA-Z0-9\-_]/', '', $key);
		if ($key === '') continue;

		// false/null は出力しない（任意）
		if ($value === false || $value === null) continue;

		// true / '' は値なし属性として出す（例: disabled, data-xxx）
		if ($value === true || $value === '') {
			$out .= ' ' . esc_attr($key);
			continue;
		}

		$out .= ' ' . esc_attr($key) . '="' . esc_attr((string) $value) . '"';
	}

	return $out;
};

// target が _blank の場合は rel に noopener noreferrer を付ける（安全対策）
$rel = ($target === '_blank') ? 'noopener noreferrer' : '';

// 専用属性（target/rel）を組み立て
$attributes = [];
if ($target) {
	$attributes[] = 'target="' . esc_attr($target) . '"';
}
if ($rel) {
	$attributes[] = 'rel="' . esc_attr($rel) . '"';
}
$attributes_string = !empty($attributes) ? ' ' . implode(' ', $attributes) : '';

// 追加属性を文字列に変換（専用属性より後に付与）
$extra_attributes_string = $build_extra_attrs_string($extra_attributes);

// hrefがある場合は<a>タグ、ない場合は<span>タグ
$tag = $href ? 'a' : 'span';
$href_attr = $href ? ' href="' . esc_url($href) . '"' : '';
?>

<<?php echo $tag; ?> class="c-button"<?php echo $href_attr; ?><?php echo $attributes_string; ?><?php echo $extra_attributes_string; ?>>
  <span class="c-button__text"><?php echo esc_html($text); ?></span>
</<?php echo $tag; ?>>

