/**
 * カウントアップのアニメーションを実行する
 * 
 * @param {number} duration - カウントアップのアニメーションの総時間（ミリ秒）
 * @param {number} interval - カウントアップの更新間隔（ミリ秒）
 * @param {function} formatWithCommas - 数値をカンマ区切りの文字列に変換する関数。変換しない場合は、formatWithCommas()を除外
 * @param {function} countUp - カウントアップを実行する関数
 * @param {boolean} counted - カウントアップが実行されたことを記録
 * 
 * @description 数値をカスタマイズする場合は、HTML要素のdata-counter属性を設定
 * 例: data-counter="500"とすることで、カウントアップの目標値を500に設定できます。
 * IntersectionObserverを使用して要素が画面内に入ったかをチェックし、カウントアップをトリガーする
 * カウントアップが実行されたら、監視を解除する
 */

const initCounter = () => {
  const counters = document.querySelectorAll('[data-counter]');
  
  if (counters.length === 0) return;

  // カウントアップのアニメーションの総時間（ミリ秒）
  const duration = 1000;
  // カウントアップの更新間隔（ミリ秒）
  const interval = 20;
  // 数値をカンマ区切りの文字列に変換する関数
  const formatWithCommas = (num) => num.toLocaleString();

  // カウントアップを実行する関数
  const countUp = (counter) => {
      // すでにカウントアップが実行されている場合は何もしない
      if (counter.dataset.counted) return;

      // カウントアップの目標値を取得
      const target = +counter.dataset.counter;
      // 1回の更新で増加するステップ値を計算
      const step = target / (duration / interval);
      let count = 0;
      // カウンターのテキストノードを取得
      const text = counter.firstChild;
      // カウンターの幅を固定するために、目標値の幅を事前に計算して設定
      const targetWidth = formatWithCommas(target).length;
      counter.style.minWidth = `${targetWidth}ch`;

      // 一定間隔でカウントアップを実行するタイマーを設定
      const timer = setInterval(() => {
          count += step;

          // 目標値に達したらカウントアップを終了
          if (count >= target) {
              count = target;
              clearInterval(timer);
          }
          // カウントをカンマ区切りで表示
          text.nodeValue = formatWithCommas(Math.floor(count));
          
          // カウントをカンマ区切りにせず表示
          // text.nodeValue = Math.floor(count);
      }, interval);
      // カウントアップが実行されたことを記録
      counter.dataset.counted = true;
  };

  // IntersectionObserverを使用して要素が画面内に入ったかをチェックし、カウントアップをトリガーする
  const countUpObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
          if (entry.isIntersecting) {
              countUp(entry.target);
              countUpObserver.unobserve(entry.target); // 一度カウントアップしたら監視を解除
          }
      });
  }, {
      threshold: 0.5 // 要素が50%表示されたときにトリガー
  });

  // すべてのカウンター要素を監視対象に追加
  counters.forEach(counter => {
      countUpObserver.observe(counter);
  });
};

// type="module"のスクリプトはDOMContentLoadedの後に実行されるため、単純に呼び出すだけで良い
initCounter();