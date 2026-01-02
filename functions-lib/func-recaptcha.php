<?php

declare(strict_types=1);

/**
 * reCAPTCHAの表示制御
 *
 * お問い合わせフォーム関連のページ（contact, confirm, thanks）以外では、
 * reCAPTCHAのスクリプトを登録解除して読み込まないようにする
 *
 * これにより、不要なページでのreCAPTCHAスクリプトの読み込みを防ぎ、
 * パフォーマンスを向上させる
 */
function ty_google_recaptcha_v3(): void {
	if (!is_page(['contact', 'confirm', 'thanks'])) {
		wp_deregister_script('google-recaptcha');
	}
}
add_action('wp_enqueue_scripts', 'ty_google_recaptcha_v3', 99);
