import "@splidejs/splide/dist/css/splide-core.min.css";
import { Splide } from "@splidejs/splide";

/**
 * トップページ メインビジュアル
 * フェードで切り替え
 */

const splideFade = document.getElementById("js-splide-fade");
if (splideFade) {
  new Splide(splideFade, {
    type: "fade",
    rewind: true,
    autoplay: true,
    speed: 2000,
    interval: 7000,
    pauseOnHover: false, // カーソルが乗ってもスクロールを停止させない
    pauseOnFocus: false, // フォーカスが当たってもスクロールを停止させない
    perPage: 1,
    perMove: 1,
    gap: 0,
    pagination: false,
    arrows: false,
  }).mount();
}
