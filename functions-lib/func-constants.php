<?php

declare(strict_types=1);

/**
 * 注意: ここで定義した値をテンプレートに出力するときは、必ず用途に合ったエスケープを行うこと（XSS 対策）。
 *
 * - プレーンテキスト（会社名・住所・電話など）→ esc_html()
 * - URL を href / src に渡す（SNS_* など）→ esc_url()
 * - HTML 断片（COMPANY_MAP の iframe など）→ wp_kses( 値, ty_map_iframe_wp_kses_allowed() ) — 詳細・早見表は functions-lib/func-kses-allowed.php 先頭のメモ参照
 *
 * wp_kses / wp_kses_post 済みの HTML に、その外側から esc_html() を重ねない（二重エスケープで表示が壊れる）。
 */

// 会社情報
const COMPANY_NAME    = '';
const COMPANY_ZIP     = ''; // 郵便番号（例: '〒123-4567'）
const COMPANY_ADDRESS = '';
const COMPANY_TEL     = '';

// アクセス用マップ等（Google マップ iframe の HTML 断片。空ならテンプレートで出さない）
const COMPANY_MAP = '';

// SNS
const SNS_INSTAGRAM_URL = '';
const SNS_X_URL         = 'https://x.com/yoshiaki_12';
const SNS_FACEBOOK_URL  = '';
const SNS_YOUTUBE_URL   = '';
const SNS_LINE_URL      = '';
