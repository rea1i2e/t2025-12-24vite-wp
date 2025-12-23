import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";

flatpickr("#js-flatpickr-date", {
  locale: Japanese,
  dateFormat: "Y-m-d",
  disableMobile: true // モバイルのネイティブUIを無効化
});