/**
 * タブ切り替え（複数セット対応）
 * タブコンテナにjs-tabを付与
 * タブトリガーにjs-tab-triggerを付与
 * ターゲットにjs-tab-targetを付与
 * タブトリガーをクリックすると、そのタブトリガーに対応するターゲットが表示される
 * タブトリガーにis-activeを付与
 * ターゲットにis-activeを付与
 * タブトリガーをクリックすると、そのタブトリガーに対応するターゲットが表示される
 * data-id属性は、タブトリガーがどのターゲットを表示するかを識別するために使用される
 * id属性は、各ターゲットを一意に識別するために使用され、data-id属性と一致する必要がある
 */
const initTab = () => {
  const tabContainers = document.querySelectorAll('.js-tab'); // 各タブコンテナを取得
  
  if (tabContainers.length === 0) return;
  
  tabContainers.forEach(tabContainer => { // 各タブコンテナごとに処理
    const tabTriggers = tabContainer.querySelectorAll('.js-tab-trigger'); // このコンテナ内のトリガーのみ取得
    const tabTargets = tabContainer.querySelectorAll('.js-tab-target'); // このコンテナ内のターゲットのみ取得
    
    tabTriggers.forEach(tabTrigger => { // 各トリガーに処理を実行
      tabTrigger.addEventListener('click', (e) => { // 各トリガーがクリックされたら
        let currentMenu = e.currentTarget // クリックされたメニューを取得
        let currentContent = tabContainer.querySelector(`#${currentMenu.dataset.id}`) // このコンテナ内の該当idのコンテンツを取得
        
        tabTriggers.forEach(trigger => { // このコンテナ内のトリガーのみ処理
          trigger.classList.remove('is-active') // すべてのトリガーのis-activeを削除
        });
        currentMenu.classList.add('is-active') // クリックされたトリガーにis-active付与
        
        tabTargets.forEach(target => { // このコンテナ内のターゲットのみ処理
          target.classList.remove('is-active') // 各コンテンツのis-activeを削除
        });
        
        if (!currentContent) return; // 該当のタブコンテンツが存在しない場合は処理を中断
        
        currentContent.classList.add('is-active') // 該当のコンテンツにis-active付与
      })
    });
  });
};

// type="module"のスクリプトはDOMContentLoadedの後に実行されるため、単純に呼び出すだけで良い
initTab();