/* ------------------------------
カレント表示（スクロール連動）
IntersectionObserver API
ビューポート中央付近に入ったセクションに応じて、ナビ項目に is-current-section を付与する。

使い方
- 監視したいセクションに class="js-section" と id（例: id="demo"）を付ける。
- ナビの li に data-section-id="セクションのid" を付ける（セクション id と一致させる）。
- セクションが画面中央付近に入ると、対応するナビ項目に is-current-section が付く。
- .js-section が1つもないページでは何も実行しない。

記述例（PHP）

▼ セクション側（例: front-page.php）
  <section class="p-demo js-section" id="demo">
    ...
  </section>
  <section class="p-demo js-section" id="demo-dialog">
    ...

▼ ナビ側（例: header.php）
  <li class="p-header__pc-nav-item" data-section-id="<?php echo esc_attr($item['slug']); ?>">
    <a href="...">...</a>
  </li>
  （外部リンクの slug の場合は data-section-id を付けない）

------------------------------ */

const SECTION_CURRENT_CLASS = 'is-current-section';
const NAV_ITEMS_SELECTOR = '[data-section-id]';

const sections = document.querySelectorAll('.js-section');

if (sections.length > 0) {
  const indexOptions = {
    root: null,
    rootMargin: '-50% 0px',
    threshold: 0,
  };
  const indexObserver = new IntersectionObserver(doWhenIntersect, indexOptions);
  sections.forEach((section) => {
    indexObserver.observe(section);
  });
}

/**
 * 交差したときに呼び出す関数
 * @param {IntersectionObserverEntry[]} entries
 */
function doWhenIntersect(entries) {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      activateIndex(entry.target);
    } else {
      deactivateSection(entry.target);
    }
  });
}

/**
 * 指定セクションをカレントとしてナビ項目のクラスを更新する
 * @param {Element} sectionEl - カレントとするセクション要素（.js-section）
 */
function activateIndex(sectionEl) {
  const sectionId = sectionEl.id;
  if (!sectionId) return;

  const navItems = document.querySelectorAll(NAV_ITEMS_SELECTOR);
  navItems.forEach((el) => {
    if (el.dataset.sectionId === sectionId) {
      el.classList.add(SECTION_CURRENT_CLASS);
    } else {
      el.classList.remove(SECTION_CURRENT_CLASS);
    }
  });
}

/**
 * セクションが中央帯から外れたとき、該当ナビ項目からのみクラスを削除する（全件走査しない）
 * @param {Element} sectionEl - 外れたセクション要素（.js-section）
 */
function deactivateSection(sectionEl) {
  const sectionId = sectionEl.id;
  if (!sectionId) return;

  const matchingNavItems = document.querySelectorAll(`[data-section-id="${CSS.escape(sectionId)}"]`);
  matchingNavItems.forEach((el) => {
    el.classList.remove(SECTION_CURRENT_CLASS);
  });
}
