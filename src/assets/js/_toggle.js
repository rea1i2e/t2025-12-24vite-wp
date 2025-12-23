/**
 * トグル機能
 * トグルアイテムにjs-toggle-itemを付与
 * トグルトリガーにjs-toggle-item-triggerを付与
 * トグルコンテンツにjs-toggle-item-contentを付与
 * トグルトリガーをクリックすると、対応するコンテンツが表示/非表示される
 */

/**
 * アニメーションの時間とイージング
 */
const animTiming = {
  duration: 400,
  easing: "ease-out"
};

/**
 * トグルを閉じるときのキーフレームを作成します。
 * @param content {HTMLElement}
 */
const closingAnimKeyframes = (content) => [
  {
    height: content.offsetHeight + 'px',
    opacity: 1,
  }, {
    height: 0,
    opacity: 0,
  }
];

/**
 * トグルを開くときのキーフレームを作成します。
 * @param content {HTMLElement}
 */
const openingAnimKeyframes = (content) => [
  {
    height: 0,
    opacity: 0,
  }, {
    height: content.offsetHeight + 'px',
    opacity: 1,
  }
];

const initToggle = () => {
  const toggleItems = document.querySelectorAll('.js-toggle-item');
  
  if (toggleItems.length === 0) return;
  
  const RUNNING_VALUE = "running"; // アニメーション実行中のときに付与する予定のカスタムデータ属性の値
  const IS_OPENED_CLASS = "is-opened"; // 開閉状態操作用のクラス名
  
  toggleItems.forEach(toggleItem => {
    const trigger = toggleItem.querySelector('.js-toggle-item-trigger');
    const content = toggleItem.querySelector('.js-toggle-item-content');
    
    if (!trigger || !content) return;
    
    trigger.addEventListener('click', () => {
      // 連打防止用。アニメーション中だったらクリックイベントを受け付けないでリターンする
      if (toggleItem.dataset.animStatus === RUNNING_VALUE) {
        return;
      }
      
      const isExpanded = trigger.getAttribute('aria-expanded') === 'true';
      
      if (isExpanded) {
        // 閉じる
        trigger.setAttribute('aria-expanded', 'false');
        toggleItem.classList.remove(IS_OPENED_CLASS);
        
        // アニメーションを実行
        const closingAnim = content.animate(closingAnimKeyframes(content), animTiming);
        // アニメーション実行中用の値を付与
        toggleItem.dataset.animStatus = RUNNING_VALUE;
        
        // アニメーションの完了後に
        closingAnim.onfinish = () => {
          content.style.display = 'none';
          // アニメーション実行中用の値を取り除く
          toggleItem.dataset.animStatus = "";
        };
      } else {
        // 開く
        trigger.setAttribute('aria-expanded', 'true');
        // アニメーション前にdisplay: blockを設定して高さを取得できるようにする
        content.style.display = 'block';
        toggleItem.classList.add(IS_OPENED_CLASS);
        
        // アニメーションを実行
        const openingAnim = content.animate(openingAnimKeyframes(content), animTiming);
        // アニメーション実行中用の値を入れる
        toggleItem.dataset.animStatus = RUNNING_VALUE;
        
        // アニメーション完了後にアニメーション実行中用の値を取り除く
        openingAnim.onfinish = () => {
          toggleItem.dataset.animStatus = "";
        };
      }
    });
  });
};

// type="module"のスクリプトはDOMContentLoadedの後に実行されるため、単純に呼び出すだけで良い
initToggle();

