/**
 * このスクリプトが期待するHTML属性（最低限）
 *
 * - フォーム本体: id="js-form"
 *   - 例) <form id="js-form"> ... </form>
 *
 * - 送信ボタン: id="js-submit"
 *   - `disabled`（＋`aria-disabled`）のみで状態管理
 *   - 見た目はCSSで `:not(:disabled)` や `[disabled]` を用いて切替
*   - 例) <button id="js-submit" type="submit">...</button>
 *
 * - 各入力要素:
 *   - 必須: `required`
 *   - 型: `type`（例: email, tel, url 等）
 *   - 形式: `pattern`（必要に応じて正規表現）
 *   - 長さ: `maxlength`（必要に応じて）
 *   - アクセシビリティ: ラベル関連（<label for> または aria-label 等）
 *   - 本スクリプトがフォーカス離脱時に `aria-invalid="true"` を付与/削除します
 *
 * スタイル連携（任意）
 * - 無効ボタン: [disabled] で見た目を薄くする
 * - アクティブボタン: `.is-active` で強調
 * - エラー枠線: `[aria-invalid="true"]` をCSSで装飾w
 */

const initCheckFormValidity = () => {
  const form = document.getElementById('js-form');
  const submitBtn = document.getElementById('js-submit');
  if (!form || !submitBtn) return;

  const update = () => {
    const valid = form.checkValidity(); // required, type=email, pattern 等をまとめて判定
    const isDisabled = !valid;
    submitBtn.disabled = isDisabled;
    if (isDisabled) {
      submitBtn.setAttribute('aria-disabled', 'true');
    } else {
      submitBtn.removeAttribute('aria-disabled');
    }
  };

  form.addEventListener('input', update, true);
  form.addEventListener('change', update, true);

  // フィールド単位のエラーハイライト（表示はCSSで）
  form.addEventListener('blur', (e) => {
    const el = e.target;
    if (el && 'checkValidity' in el) {
      const invalid = !el.checkValidity();
      if (invalid) {
        el.setAttribute('aria-invalid', 'true');
      } else {
        el.removeAttribute('aria-invalid');
      }
    }
  }, true);

  // 送信時の最終バリデーション（Enter送信含む）
  form.addEventListener('submit', (e) => {
    if (!form.checkValidity()) {
      e.preventDefault();
      form.reportValidity();
    }
  });

  update();
};

// type="module"のスクリプトはDOMContentLoadedの後に実行されるため、単純に呼び出すだけで良い
initCheckFormValidity();
