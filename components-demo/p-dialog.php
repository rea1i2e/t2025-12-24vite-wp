<?php
$dialogs = [
  [
    'title' => "一つ目のモーダルコンテンツ",
    'trigger' => "Sample 01",
    'descriptions' => [
      "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
      "<a href='#'>Focus Test</a>"
    ]
  ],
  [
    'title' => "二つ目のモーダルコンテンツ",
    'trigger' => "Sample 02",
    'descriptions' => [
      "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
      "<a href='#'>Focus Test</a>"
    ]
  ],
  [
    'title' => "三つ目のモーダルコンテンツ",
    'trigger' => "Sample 03",
    'descriptions' => [
      "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.",
      "<a href='#'>Focus Test</a>"
    ]
  ]
];
?>
<div class="">
  <?php foreach ($dialogs as $index => $dialog) : ?>
    <button class="p-dialog-trigger" type="button" data-modal-open="modal<?php echo $index + 1; ?>">
      <?php echo $dialog['trigger']; ?>
    </button>
    <dialog id="modal<?php echo $index + 1; ?>" class="p-dialog js-dialog" <?php echo $index === 0 ? 'autofocus' : ''; ?>>
      <div class="p-dialog__container">
        <h1 class="p-dialog__title"><?php echo $dialog['title']; ?></h1>
        <?php foreach ($dialog['descriptions'] as $description) : ?>
          <p class="p-dialog__description"><?php echo $description; ?></p>
        <?php endforeach; ?>
      </div>
      <button class="close" type="button" aria-labelledby="close<?php echo $index + 1; ?>" data-modal-close>
        <span id="close<?php echo $index + 1; ?>" style="display:none">モーダルを閉じる</span>
      </button>
    </dialog>
  <?php endforeach; ?>
</div>


