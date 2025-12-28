<?php

declare(strict_types=1);

/**
 * Ensure module scripts are output with type="module" (especially in dev).
 */
function ty_script_loader_tag(string $tag, string $handle, string $src): string {
	if (strpos($handle, 't2025-') !== 0) return $tag;
	if (strpos($tag, ' type=') !== false) return $tag;
	return str_replace('<script ', '<script type="module" ', $tag);
}
add_filter('script_loader_tag', 'ty_script_loader_tag', 10, 3);


