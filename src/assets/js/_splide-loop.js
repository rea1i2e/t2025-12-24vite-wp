/**
 * 無限ループスライダー
 * @see https://splidejs.com/extensions/auto-scroll/
 */

import "@splidejs/splide/dist/css/splide-core.min.css";
import { Splide } from "@splidejs/splide";
import { AutoScroll } from "@splidejs/splide-extension-auto-scroll";

const splideLoopOptions = {
  arrows: false, // 矢印ボタンを非表示
  pagination: false, // ページネーションを非表示
  type: "loop", // ループさせる
  autoWidth: true, // cssで幅指定
  // gap: "calc(24 / 16 * 1rem)", // スライド間の余白
  drag: true, // スマホで動作が不安定なので、実機確認必須
  autoScroll: {
    speed: 0.5, // スクロール速度
    pauseOnHover: false, // カーソルが乗ってもスクロールを停止させない
    pauseOnFocus: false, // フォーカスが当たってもスクロールを停止させない
  },
};

const splideLoopElement = document.querySelector("#js-splide-loop");
if (splideLoopElement) {
  const splideLoopInstance = new Splide(splideLoopElement, splideLoopOptions);
  splideLoopInstance.mount({ AutoScroll });
}
