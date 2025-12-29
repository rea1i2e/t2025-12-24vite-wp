<?php
declare(strict_types=1);

// タブの内容（データ）
$tab_groups = [
	[
		'tabs' => [
			['id' => 'tab1-01', 'label' => 'タブ1-1', 'content' => 'タブコンテンツ1-1', 'active' => true],
			['id' => 'tab1-02', 'label' => 'タブ1-2', 'content' => 'タブコンテンツ1-2', 'active' => false],
			['id' => 'tab1-03', 'label' => 'タブ1-3', 'content' => 'タブコンテンツ1-3', 'active' => false],
		],
	],
	[
		'tabs' => [
			['id' => 'tab2-01', 'label' => 'タブ2-1', 'content' => 'タブコンテンツ2-1', 'active' => true],
			['id' => 'tab2-02', 'label' => 'タブ2-2', 'content' => 'タブコンテンツ2-2', 'active' => false],
			['id' => 'tab2-03', 'label' => 'タブ2-3', 'content' => 'タブコンテンツ2-3', 'active' => false],
		],
	],
];
?>

<?php foreach ($tab_groups as $group) : ?>
	<?php $tabs = (isset($group['tabs']) && is_array($group['tabs'])) ? $group['tabs'] : []; ?>
<div class="p-tab js-tab">
  <ul class="p-tab__menu-items">
			<?php foreach ($tabs as $i => $tab) : ?>
				<?php
					$id = isset($tab['id']) ? (string) $tab['id'] : '';
					$label = isset($tab['label']) ? (string) $tab['label'] : '';
					$is_active = !empty($tab['active']);
				?>
    <li class="p-tab__menu-item">
					<button
						type="button"
						class="p-tab__button js-tab-trigger<?php echo $is_active ? ' is-active' : ''; ?>"
						data-id="<?php echo esc_attr($id); ?>"
					><?php echo esc_html($label); ?></button>
    </li>
			<?php endforeach; ?>
  </ul>

  <div class="p-tab__contents">
			<?php foreach ($tabs as $i => $tab) : ?>
				<?php
					$id = isset($tab['id']) ? (string) $tab['id'] : '';
					$content = isset($tab['content']) ? (string) $tab['content'] : '';
					$is_active = !empty($tab['active']);
					$content_class = 'p-tab__content js-tab-target' . ($is_active ? ' is-active' : '');
				?>
				<div class="<?php echo esc_attr($content_class); ?>" id="<?php echo esc_attr($id); ?>">
					<?php echo esc_html($content); ?>
    </div>
			<?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>


