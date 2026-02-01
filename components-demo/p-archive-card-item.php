<?php
/**
 * デモカードアイテムのテンプレートパーツ
 * 
 * グローバル$postを使用します。
 * 呼び出し側でループ（have_posts() / the_post()）を実行している必要があります。
 */
global $post;

if (!$post) {
    return;
}
?>
<article class="p-demo-cards__item">
  <a href="<?php the_permalink(); ?>" class="p-demo-cards__item-link">
    <figure class="p-demo-cards__image">
      <?php ty_display_thumbnail('full'); ?>
    </figure>
    <h2 class="p-demo-cards__title"><?php the_title(); ?></h2>
  </a>
</article>
