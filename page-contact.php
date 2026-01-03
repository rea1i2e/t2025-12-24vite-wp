<?php get_header(); ?>
<main class="p-main-sub">
  <?php get_template_part('components/breadcrumb') ?>
  <div class="l-contact p-contact">
    <div class="p-contact__inner l-inner">
      <?php echo do_shortcode('[contact-form-7 id="dd07177" title="お問い合わせ（送信）"]'); ?>
    </div>
  </div>
</main>
<?php get_footer(); ?>
<?php /*
<div class="p-form">
<dl class="p-form__dl">
<dt class="p-form__dt">お問い合わせの内容（いずれかにチェックを入れてください）</dt>
<dd class="p-form__dd">[radio inquiry-type1 id:inquiry-type1 use_label_element default:1 "お取引に関するお問い合わせ" "採用に関するお問い合わせ" "その他のお問い合わせ"]</dd>
<dt class="p-form__dt">お問い合わせの内容（いずれかにチェックを入れてください）</dt>
<dd class="p-form__dd">[checkbox inquiry-type2 id:inquiry-type2 use_label_element "サービスの詳細について知りたい" "料金について知りたい" "資料ご請求" "その他のお問い合わせ"]</dd>
<dt class="p-form__dt">お問い合わせの内容（いずれかにチェックを入れてください）</dt>
<dd class="p-form__dd">
<div class="p-form__select-wrap">[select* inquiry-type3 id:inquiry-type3 include_blank "サービスの詳細について知りたい" "料金について知りたい" "資料ご請求" "その他のお問い合わせ"]</div>
</dd>
<dt class="p-form__dt"><label for="your-company">会社名</label></dt>
<dd class="p-form__dd">[text* your-company id:your-company]</dd>
<dt class="p-form__dt"><label for="your-name">お名前（必須）</label></dt>
<dd class="p-form__dd">[text* your-name id:your-name autocomplete:name]</dd>
<dt class="p-form__dt"><label for="your-email">メールアドレス（必須）</label></dt>
<dd class="p-form__dd">[email* your-email id:your-email autocomplete:email]</dd>
<dt class="p-form__dt"><label for="your-tel">電話番号</label></dt>
<dd class="p-form__dd">[tel your-tel id:your-tel]</dd>
<dt class="p-form__dt"><label for="your-message">メッセージ本文</label></dt>
<dd class="p-form__dd">[textarea* your-message id:your-message placeholder "お問い合わせ内容はできるだけ詳しく、具体的にご入力ください。"]</dd>
</dl>
<p class="p-form__policy-description">
<a href="./privacy/" target="_blank" rel="noopener noreferrer">個人情報保護ポリシー</a>をご覧いただき、ご入力いただいた情報の取り扱いについてご確認・ご同意のうえ、ご送信ください。
</p>
<div class="p-form__acceptance">[acceptance your-acceptance id:your-acceptance]個人情報保護ポリシーに同意する（チェックを入れて下さい）（必須）[/acceptance]</div>
<div class="p-form__send">[submit "同意して送信する"]</div>
</div>
*/ ?>