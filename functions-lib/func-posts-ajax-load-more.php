<?php

declare(strict_types=1);

/**
 * アーカイブページの「もっと見る」機能
 */

/**
 * Ajaxハンドラーの登録
 * 
 * WordPressのAjax APIでは、ログイン状態に応じて異なるアクションフックを使用する必要があります。
 * - wp_ajax_*: ログイン済みユーザーからのリクエストを処理
 * - wp_ajax_nopriv_*: 未ログインユーザー（一般訪問者）からのリクエストを処理
 * 
 * 両方に同じ関数を登録することで、ログイン状態に関わらず同じ処理を実行できます。
 * どちらか一方だけだと、ログイン状態によって機能が動作しなくなります。
 */
add_action('wp_ajax_load_more_posts', 'ty_load_more_posts');
add_action('wp_ajax_nopriv_load_more_posts', 'ty_load_more_posts');

/**
 * 追加の投稿を取得してHTMLを返す
 */
function ty_load_more_posts(): void
{
    // セキュリティチェック
    check_ajax_referer('load_more_posts', 'nonce');

    $page = isset($_POST['page']) ? (int) $_POST['page'] : 1;
    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'post';
    $posts_per_page = isset($_POST['posts_per_page']) ? (int) $_POST['posts_per_page'] : 6;

    $args = [
        'post_type' => $post_type,
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'post_status' => 'publish',
    ];

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('components-demo/p-archive-card-item');
        }
        wp_reset_postdata();
        $html = ob_get_clean();

        wp_send_json_success([
            'html' => $html,
            'has_more' => $page < $query->max_num_pages,
        ]);
    } else {
        wp_send_json_error(['message' => '投稿が見つかりませんでした。']);
    }
}

/**
 * Ajax用のJavaScript変数を出力
 * Viteのモジュールスクリプトではwp_localize_scriptが正しく動作しない可能性があるため、
 * wp_footerでインラインスクリプトとして直接出力する
 */
add_action('wp_footer', 'ty_output_archive_ajax_script', 5);
function ty_output_archive_ajax_script(): void
{
    if (!is_archive() && !is_home()) {
        return;
    }

    $post_type = get_post_type();
    if (!$post_type) {
        $post_type = 'post';
    }

    // 現在のクエリから投稿数を取得
    global $wp_query;
    $posts_per_page = isset($wp_query->query_vars['posts_per_page']) 
        ? (int) $wp_query->query_vars['posts_per_page'] 
        : get_option('posts_per_page');

    $ajax_data = [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('load_more_posts'),
        'post_type' => $post_type,
        'posts_per_page' => $posts_per_page,
    ];
    ?>
    <script type="text/javascript">
    window.archiveAjax = <?php echo wp_json_encode($ajax_data); ?>;
    </script>
    <?php
}
