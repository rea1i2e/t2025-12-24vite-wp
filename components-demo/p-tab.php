<?php
declare(strict_types=1);

$tabs = [
	['id' => 'tab1-01', 'label' => 'タブ1-1', 'content' => 'タブコンテンツ1-1', 'active' => true],
	['id' => 'tab1-02', 'label' => 'タブ1-2', 'content' => 'タブコンテンツ1-2', 'active' => false],
	['id' => 'tab1-03', 'label' => 'タブ1-3', 'content' => 'タブコンテンツ1-3', 'active' => false],
];
?>

<div class="p-tab js-tab">
  <ul class="p-tab__menu-items">
		<?php foreach ($tabs as $i => $tab) : ?>
    <li class="p-tab__menu-item">
			<button
				type="button"
				class="p-tab__button js-tab-trigger<?php echo !empty($tab['active']) ? ' is-active' : ''; ?>"
				data-id="<?php echo $tab['id']; ?>"
			><?php echo $tab['label']; ?></button>
    </li>
		<?php endforeach; ?>
  </ul>
  <div class="p-tab__contents">
		<?php foreach ($tabs as $i => $tab) : ?>
			<div class="p-tab__content js-tab-target<?php echo !empty($tab['active']) ? ' is-active' : ''; ?>" id="<?php echo esc_attr($tab['id']); ?>">
				<?php echo esc_html($tab['content']); ?>
			</div>
		<?php endforeach; ?>
  </div>
</div>

<p>2セット設置しても、それぞれ独立して動作</p>
<div class="p-tab js-tab">
  <ul class="p-tab__menu-items">
		<?php foreach ($tabs as $i => $tab) : ?>
    <li class="p-tab__menu-item">
			<button
				type="button"
				class="p-tab__button js-tab-trigger<?php echo !empty($tab['active']) ? ' is-active' : ''; ?>"
				data-id="<?php echo $tab['id']; ?>"
			><?php echo $tab['label']; ?></button>
    </li>
		<?php endforeach; ?>
  </ul>
  <div class="p-tab__contents">
		<?php foreach ($tabs as $i => $tab) : ?>
			<div class="p-tab__content js-tab-target<?php echo !empty($tab['active']) ? ' is-active' : ''; ?>" id="<?php echo esc_attr($tab['id']); ?>">
				<?php echo esc_html($tab['content']); ?>
			</div>
		<?php endforeach; ?>
  </div>
</div>


