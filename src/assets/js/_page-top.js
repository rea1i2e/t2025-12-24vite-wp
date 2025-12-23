/**
 * ページトップボタンの表示制御
 * IntersectionObserver APIを使用して、特定のセクションがビューポートに入っているかを監視し、
 * ページトップボタンの表示を制御します。
 */
document.addEventListener("DOMContentLoaded", () => {
  // ページトップボタン、メインビジュアルセクション、フッターを取得
  const pageTop = document.getElementById("js-page-top");
  const mvSection = document.getElementById("js-mv");
  const footer = document.getElementById("footer");
  
  // ページトップボタンが存在しない場合は処理を終了
  if (!pageTop) {
    return;
  }
  
  // メインビジュアルとフッターの可視状態を管理するフラグ
  // mvSectionが存在しない場合は、初期状態をfalseに設定（常にボタンを表示可能にする）
  let isMvVisible = mvSection ? true : false;
  let isFooterVisible = false;

  // ページトップボタンの表示状態を更新する関数
  const updateBannerVisibility = () => {
    if (!pageTop) return;
    
    // メインビジュアルとフッターが両方ともビューポートに入っていない場合にボタンを表示
    if (!isMvVisible && !isFooterVisible) {
      pageTop.classList.add("is-show");
    } else {
      pageTop.classList.remove("is-show");
    }
  };

  // メインビジュアルセクションが存在する場合のみ監視を設定
  if (mvSection) {
    // メインビジュアルセクションのIntersectionObserverのオプション
    const mvObserverOptions = {
      threshold: 0.9, // 90%がビューポートに入ったときに発火
    };

    // メインビジュアルセクションの可視状態を監視するコールバック関数
    const mvObserverCallback = ([entry]) => {
      isMvVisible = entry.isIntersecting; // ビューポートに入っているかを判定
      updateBannerVisibility(); // ボタンの表示状態を更新
    };

    // メインビジュアルセクション用のIntersectionObserverを作成
    const mvObserver = new IntersectionObserver(mvObserverCallback, mvObserverOptions);
    
    // メインビジュアルセクションを監視対象に追加
    mvObserver.observe(mvSection);
  }

  // フッターが存在する場合のみ監視を設定
  if (footer) {
    // フッターのIntersectionObserverのオプション
    const footerObserverOptions = {
      threshold: 0, // 1ピクセルでもビューポートに入ったら発火
    };

    // フッターの可視状態を監視するコールバック関数
    const footerObserverCallback = ([entry]) => {
      isFooterVisible = entry.isIntersecting; // ビューポートに入っているかを判定
      updateBannerVisibility(); // ボタンの表示状態を更新
    };

    // フッター用のIntersectionObserverを作成
    const footerObserver = new IntersectionObserver(footerObserverCallback, footerObserverOptions);
    
    // フッターを監視対象に追加
    footerObserver.observe(footer);
  }
  
  // 初期状態を更新（mvSectionが存在しない場合はボタンを表示）
  updateBannerVisibility();
});