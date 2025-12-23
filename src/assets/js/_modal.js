// モーダル関連の処理
const initModal = () => {
  const modalBtns = document.querySelectorAll("[data-target]");
  
  if (modalBtns.length === 0) return;
  
  modalBtns.forEach((btn) => {
    let touchStartX = 0;
    let touchStartY = 0;
    let touchStartTime = 0;
    let touchHandled = false; // タッチデバイスでのタップ後のクリックイベントを防ぐためのフラグ
    
    // クリックイベントの処理
    const openModal = () => {
      const modal = btn.getAttribute("data-target");
      const modalElement = document.getElementById(modal);
      if (!modalElement) return;
      
      modalElement.classList.add("is-show");
      document.documentElement.style.overflow = "hidden";
      document.body.style.overflow = "hidden";
    };
    
    // タッチ開始位置を記録
    btn.addEventListener('touchstart', (e) => {
      touchHandled = false; // タッチ開始時にリセット
      touchStartX = e.touches[0].clientX;
      touchStartY = e.touches[0].clientY;
      touchStartTime = Date.now();
    });
    
    // デスクトップおよびタッチデバイス用のクリックイベント
    btn.addEventListener('click', (e) => {
      // タッチイベントが処理された場合はクリックを無視
      if (!touchHandled) {
        openModal();
      }
    });
    
    // タッチ終了時に移動距離をチェック
    btn.addEventListener('touchend', (e) => {
      const touchEndX = e.changedTouches[0].clientX;
      const touchEndY = e.changedTouches[0].clientY;
      const touchEndTime = Date.now();
      
      // 移動距離を計算
      const deltaX = Math.abs(touchEndX - touchStartX);
      const deltaY = Math.abs(touchEndY - touchStartY);
      const deltaTime = touchEndTime - touchStartTime;
      
      // 移動距離が10px未満かつタッチ時間が300ms未満の場合のみタップと判定
      if (deltaX < 10 && deltaY < 10 && deltaTime < 300) {
        e.preventDefault();
        openModal();
        touchHandled = true; // タッチイベントが処理されたことを記録
      }
    });
  });

  // 1つ目のモーダルを常時表示 // test
  const firstModal = document.querySelector("[data-modal]");
  if (firstModal) {
    firstModal.classList.add("is-show");
    document.documentElement.style.overflow = "hidden";
    document.body.style.overflow = "hidden";
  }

  const closeBtns = document.querySelectorAll("[data-modal-close]");
  closeBtns.forEach((btn) => {
    btn.onclick = () => {
      const modal = btn.closest("[data-modal]");
      if (!modal) return;
      
      modal.classList.remove("is-show");
      if (document.querySelectorAll(".is-show").length === 0) {
        document.documentElement.style.overflow = "";
        document.body.style.overflow = "";
      }
    };
  });

  window.onclick = (event) => {
    if (event.target.getAttribute("data-modal") !== null) {
      event.target.classList.remove("is-show");
      if (document.querySelectorAll(".is-show").length === 0) {
        document.documentElement.style.overflow = "";
        document.body.style.overflow = "";
      }
    }
  };
};

// type="module"のスクリプトはDOMContentLoadedの後に実行されるため、単純に呼び出すだけで良い
initModal();