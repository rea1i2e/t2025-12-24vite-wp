// ドロワーメニュー関連の処理
const initDrawer = () => {
  const menuButton = document.getElementById("js-menu");
  const drawer = document.getElementById("js-drawer");
  const drawerMenu = document.getElementById("js-drawer-menu");

  if (!menuButton || !drawer || !drawerMenu) return;

  const drawerAnchorLinks = drawerMenu.querySelectorAll('a[href*="#"]');

  // ドロワーメニューを展開する処理
  // scrollbar-gutter: stable（kiso.css :root）を前提に、paddingRight / menuButton の right 補正は行わない
  const openDrawer = () => {
    menuButton.setAttribute("aria-expanded", "true");
    drawer.setAttribute("aria-hidden", "false");
    drawer.inert = false;
    document.documentElement.style.overflow = "hidden";
    document.body.style.overflow = "hidden";
  };

  // ドロワーメニューを閉じる処理
  const closeDrawer = () => {
    menuButton.setAttribute("aria-expanded", "false");
    drawer.setAttribute("aria-hidden", "true");
    drawer.inert = true;
    document.documentElement.style.overflow = "";
    document.body.style.overflow = "";
  };

  // ハンバーガーメニューをクリックした時の処理
  menuButton.addEventListener("click", () => {
    if (menuButton.getAttribute("aria-expanded") === "true") {
      closeDrawer();
    } else {
      openDrawer();
    }
  });

  // ページ内リンクをクリックしたとき、ドロワーメニューを閉じる
  drawerAnchorLinks.forEach((link) => {
    link.addEventListener("click", () => {
      closeDrawer();
    });
  });

  // ドロワーメニュー以外の要素をクリックしたとき、ドロワーメニューを閉じる
  drawer.addEventListener("click", (event) => {
    if (
      (drawerMenu && drawerMenu.contains(event.target)) ||
      (menuButton && menuButton.contains(event.target))
    )
      return;

    closeDrawer();
  });

  // ブレイクポイントを超えたとき、ドロワーメニューを閉じる
  const mediaQuery = window.matchMedia('(min-width: 768px)');
  const handleMediaChange = (e) => {
    if (e.matches) {
      closeDrawer();
    }
  };

  // 初期状態のチェック
  if (mediaQuery.matches) {
    closeDrawer();
  }

  // メディアクエリの変更を監視
  mediaQuery.addEventListener('change', handleMediaChange);
};

// type="module"のスクリプトはDOMContentLoadedの後に実行されるため、単純に呼び出すだけで良い
initDrawer();
