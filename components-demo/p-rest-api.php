<?php
/**
 * 他サイト（WordPress）の REST API から投稿を取得し、日付とタイトルを表示するデモ。
 *
 * エンドポイント: /wp-json/wp/v2/posts（標準の投稿一覧）
 * 取得件数はクエリ per_page で指定。
 */
// 対象サイトの投稿API。orderby はデフォルトで date desc（新しい順）。
$ty_rest_api_url = 'https://acinc.design/wp-json/wp/v2/posts?per_page=3';

// WordPress 標準の HTTP クライアント。file_get_contents よりタイムアウト・エラー扱いが明確。
$ty_rest_response = wp_remote_get($ty_rest_api_url, [
    'timeout' => 10,
    'headers' => [
        'Accept' => 'application/json',
    ],
]);

$ty_rest_posts = [];
// 通信エラーでなく、かつ 200 OK のときだけ本文を配列として採用する。
if (!is_wp_error($ty_rest_response) && (int) wp_remote_retrieve_response_code($ty_rest_response) === 200) {
    $ty_rest_body = wp_remote_retrieve_body($ty_rest_response);
    $ty_rest_decoded = json_decode($ty_rest_body, true);
    if (is_array($ty_rest_decoded)) {
        $ty_rest_posts = $ty_rest_decoded;
    }
}
?>
<section class="p-rest-api l-rest-api" id="rest-api">
  <div class="p-rest-api__inner l-inner">
   <h2 class="p-rest-api__heading">
    REST APIを使ったデータ取得
   </h2>
   <div class="p-rest-api__content">
    <?php
    // 取得失敗時はリストを出さない（見出しのみ）。
    if ($ty_rest_posts !== []) :
        ?>
      <ul class="p-rest-api__list">
        <?php foreach ($ty_rest_posts as $ty_rest_item) : ?>
          <?php
          // レスポンスは外部由来のため、想定キーがあるかだけ確認。
          if (!is_array($ty_rest_item) || !isset($ty_rest_item['date'], $ty_rest_item['title']['rendered'])) {
              continue;
          }
          $ty_rest_date_raw = $ty_rest_item['date'];
          // API の date は「そのサイトのローカル日時」。先頭 Y-m-d だけ使い表示し、自サーバTZの解釈ずれを避ける。
          $ty_rest_date_display = str_replace('-', '.', substr($ty_rest_date_raw, 0, 10));
          // title.rendered は HTML 可。プレーンテキスト表示のためタグを除去。
          $ty_rest_title_display = wp_strip_all_tags($ty_rest_item['title']['rendered']);
          ?>
          <li class="p-rest-api__item">
            <time class="p-rest-api__date" datetime="<?php echo esc_attr($ty_rest_date_raw); ?>"><?php echo esc_html($ty_rest_date_display); ?></time>
            <span class="p-rest-api__title"><?php echo esc_html($ty_rest_title_display); ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
   </div>
  </div>
</section>
