/**
 * selectの初期値文字色を制御
 * .p-form.scssで--placeholder-colorを設定し、その値を取得して設定
 */
const initOptionColor = () => {
  // .p-form要素からCSS変数を取得
  const formElement = document.querySelector('.p-form');
  if (!formElement) return;
  
  const placeholderColor = getComputedStyle(formElement).getPropertyValue('--placeholder-color').trim();
  
  document.querySelectorAll('select').forEach((select) => {
    // 初期状態のチェック
    if (select.value === "") {
      select.style.color = placeholderColor;
    }
    
    select.addEventListener('change', () => {
      if (select.value === "") {
        select.style.color = placeholderColor;
      } else {
        select.style.color = "";
      }
    });
  });
};

// type="module"のスクリプトはDOMContentLoadedの後に実行されるため、単純に呼び出すだけで良い
initOptionColor();