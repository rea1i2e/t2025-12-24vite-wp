<?php

declare(strict_types=1);

/**
 * Contact Form 7 — フォームテンプレートの autop 無効化
 *
 * CF7 既定ではフォームタグの前後に <p> / <br> を自動挿入する。
 * 本テーマは p-form BEM（dl / dt / dd / .p-form__select-wrap 等）で HTML を組むため、
 * 全 CF7 フォームで autop を止める（the_content の wpautop とは独立）。
 *
 * 方針の正本: ナレッジ wiki/wordpress-contact-form-7-autop.md
 */
add_filter('wpcf7_autop_or_not', '__return_false');
