/**
 * スムーススクロール
 * @see https://www.tak-dcxi.com/article/smooth-scroll-implementation-example/
 * ・TypeScript → JavaScript
 * ・ページ外からも機能させた上で、移動は瞬時に
 * ・フォーカスは無効化
 */

const initializeSmoothScroll = () => {
  // クリックイベントのリスナーを追加
  document.addEventListener('click', handleClick, { capture: true })
  
  // ページ読み込み時のハッシュ処理
  if (window.location.hash) {
    handleHashOnLoad()
  }
  
  // ハッシュ変更時の処理
  window.addEventListener('hashchange', handleHashChange)
}

// ページ読み込み時のハッシュ処理
const handleHashOnLoad = () => {
  // type="module"のスクリプトはDOMContentLoadedの後に実行されるため、単純に処理を実行
  setTimeout(processHash, 100)
}

// ハッシュ変更時の処理
const handleHashChange = () => {
  if (window.location.hash) {
    processHash()
  }
}

// ハッシュの処理
const processHash = () => {
  const hash = window.location.hash
  const target = document.getElementById(decodeURIComponent(hash.slice(1))) || 
                 (hash === '#top' && document.body)
  
  if (target) {
    // ページ外からのアクセスは瞬時に移動
    scrollToTargetInstant(target)
    // focusTarget(target)
  }
}

// 瞬時スクロール（ページ外からのアクセス用）
const scrollToTargetInstant = (element) => {
  const headerBlockSize = getHeaderBlockSize()
  // 固定配置のヘッダーのブロックサイズを`scrollMarginBlockStart`に設定
  element.style.scrollMarginBlockStart = headerBlockSize
  // 瞬時にスクロール
  element.scrollIntoView({ behavior: 'instant', inline: 'end' })
}

// 固定配置のヘッダーのブロックサイズを取得
const getHeaderBlockSize = () => {
  const header = document.querySelector('[data-fixed-header]')

  if (!header) return '0'

  const { position, blockSize } = window.getComputedStyle(header)
  const isFixed = position === 'fixed' || position === 'sticky'

  return isFixed ? blockSize : '0'
}

const scrollToTarget = (element) => {
  const headerBlockSize = getHeaderBlockSize()
  // 固定配置のヘッダーのブロックサイズを`scrollMarginBlockStart`に設定
  element.style.scrollMarginBlockStart = headerBlockSize
  // ユーザーが視差効果を減らす設定をしているかどうかを判定
  const isPrefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  // 視差効果を減らす設定がされている場合は 'instant'、そうでない場合は 'smooth' にスクロール動作を設定
  const scrollBehavior = isPrefersReduced ? 'instant' : 'smooth'
  // 縦書きの場合は左スクロール、横書きの場合は上スクロールを実行
  element.scrollIntoView({ behavior: scrollBehavior, inline: 'end' })
}

// const focusTarget = (element) => {
//   // ターゲット要素にフォーカスを設定
//   element.focus({ preventScroll: true })
//   // アクティブな要素がターゲット要素でない場合
//   if (document.activeElement !== element) {
//     // ターゲット要素のtabindexを一時的に-1に設定
//     element.setAttribute('tabindex', '-1')
//     // 再度フォーカスを設定
//     element.focus({ preventScroll: true })
//   }
// }

const handleClick = (event) => {
  // クリックされたボタンが左ボタンでない場合は処理を中断
  if (event.button !== 0) return
  // クリックされたリンク要素を取得
  const currentLink = event.target.closest('a[href*="#"]')
  if (!currentLink) return

  const hash = currentLink.hash
  // スムーススクロールを無効にする条件をチェックし、スムーススクロールを無効にする場合は処理を中断
  if (
    !hash ||
    currentLink.getAttribute('role') === 'tab' ||
    currentLink.getAttribute('role') === 'button' ||
    currentLink.getAttribute('data-smooth-scroll') === 'disabled'
  )
    return

  // アンカーリンクのハッシュ部分からターゲット要素を取得
  const target = document.getElementById(decodeURIComponent(hash.slice(1))) || (hash === '#top' && document.body)

  if (target) {
    // デフォルトのリンク遷移を防止
    event.preventDefault()
    // ターゲット要素までスムーズにスクロール
    scrollToTarget(target)
    // ターゲット要素にフォーカスを設定
    // focusTarget(target)
    // ブラウザの履歴にアンカーリンクのハッシュを追加
    if (!(hash === '#top')) {
      history.pushState({}, '', hash)
    }
  }
}

// export default initializeSmoothScroll
initializeSmoothScroll();