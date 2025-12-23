/**
 * サムネイル付きスライダー
 * メインスライダーとサムネイルスライダーを同期
 * @see https://splidejs.com/tutorials/thumbnail-carousel/
 */

import "@splidejs/splide/dist/css/splide-core.min.css";
import { Splide } from "@splidejs/splide";


const mainElement = document.getElementById("js-splide-thumbnail-main");
const thumbnailsElement = document.getElementById("js-splide-thumbnail-nav");

if (mainElement && thumbnailsElement) {
  const main = new Splide(mainElement, {
    type: "fade",
    pagination: false,
    arrows: false,
  });
  
  const thumbnails = new Splide(thumbnailsElement, {
    rewind: true,
    type: "loop",
    autoplay: true, // 自動再生
    perPage: 4,
    isNavigation: true,
    // gap: "calc(24 / 16 * 1rem)",
    focus: "center",
    pagination: false,
    // arrows: false,
    breakpoints: {
      768: {
        // gap: "calc(10 / 16 * 1rem)",
        perPage: 1,
        padding: "calc(48 / 16 * 1rem)",
      },
    },
  });

  main.sync(thumbnails);
  main.mount();
  thumbnails.mount();
}


