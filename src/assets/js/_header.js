const initHeader = () => {
  // メインビジュアルセクションとヘッダーを取得
  const mvSection = document.getElementById("js-mv");
  const header = document.getElementById("js-header");

  if (!mvSection || !header) return;

  // メインビジュアルの可視状態を管理するフラグ
  let isMvVisible = true;

  // ヘッダーの表示状態を更新する関数
  const updateHeaderVisibility = () => {
    // メインビジュアルがビューポートに入っていない場合にヘッダーを表示
    if (!isMvVisible) {
      header.classList.add("is-show");
    } else {
      header.classList.remove("is-show");
    }
  };

  // CSS変数からヘッダーの高さを取得し、remをpxに変換
  const headerHeightRem = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--header-height'));
  const rootFontSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
  const headerHeightPx = headerHeightRem * rootFontSize;
  
  // メインビジュアルセクションのIntersectionObserverのオプション
  const mvObserverOptions = {
    rootMargin: `-${headerHeightPx + 1}px 0px 0px 0px`, // ヘッダー分のオフセット（px単位）
    threshold: 0, // 1ピクセルでもビューポートに入ったら発火
  };

  // メインビジュアルセクションの可視状態を監視するコールバック関数
  const mvObserverCallback = ([entry]) => {
    isMvVisible = entry.isIntersecting; // ビューポートに入っているかを判定
    updateHeaderVisibility(); // ヘッダーの表示状態を更新
  };

  // メインビジュアルセクション用のIntersectionObserverを作成
  const mvObserver = new IntersectionObserver(
    mvObserverCallback,
    mvObserverOptions
  );

  // メインビジュアルセクションを監視対象に追加
  mvObserver.observe(mvSection);
};

// type="module"のスクリプトはDOMContentLoadedの後に実行されるため、単純に呼び出すだけで良い
initHeader();