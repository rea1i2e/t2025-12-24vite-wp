/**
 * アーカイブページの「もっと見る」機能
 */

const initArchiveLoadMore = () => {
  const loadMoreBtn = document.getElementById('js-load-more');
  const container = document.querySelector('.p-demo-cards');

  // デバッグ: 要素の存在確認
  console.log('[Archive Load More] 初期化開始');
  console.log('[Archive Load More] loadMoreBtn:', loadMoreBtn);
  console.log('[Archive Load More] container:', container);
  console.log('[Archive Load More] window.archiveAjax:', window.archiveAjax);
  
  if (!loadMoreBtn) {
    console.warn('[Archive Load More] ボタンが見つかりません: #js-load-more');
    return;
  }
  
  if (!container) {
    console.warn('[Archive Load More] コンテナが見つかりません: .p-demo-cards');
    return;
  }
  
  if (!window.archiveAjax) {
    console.warn('[Archive Load More] window.archiveAjax が定義されていません');
    return;
  }
  
  console.log('[Archive Load More] 初期化成功');

  let currentPage = 1;
  let isLoading = false;

  loadMoreBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    
    console.log('[Archive Load More] ボタンクリック');
    
    if (isLoading) {
      console.log('[Archive Load More] 既に読み込み中です');
      return;
    }

    isLoading = true;
    loadMoreBtn.disabled = true;
    loadMoreBtn.textContent = '読み込み中...';

    try {
      const nextPage = currentPage + 1;
      console.log('[Archive Load More] リクエスト送信:', {
        page: nextPage,
        post_type: window.archiveAjax.post_type,
        posts_per_page: window.archiveAjax.posts_per_page,
        ajax_url: window.archiveAjax.ajax_url,
      });

      const formData = new FormData();
      formData.append('action', 'load_more_posts');
      formData.append('nonce', window.archiveAjax.nonce);
      formData.append('page', nextPage);
      formData.append('post_type', window.archiveAjax.post_type);
      formData.append('posts_per_page', window.archiveAjax.posts_per_page);

      const response = await fetch(window.archiveAjax.ajax_url, {
        method: 'POST',
        body: formData,
      });

      console.log('[Archive Load More] レスポンス受信:', response.status, response.statusText);

      const data = await response.json();
      console.log('[Archive Load More] レスポンスデータ:', data);

      if (data.success && data.data.html) {
        console.log('[Archive Load More] HTML追加成功');
        container.insertAdjacentHTML('beforeend', data.data.html);
        currentPage++;
        console.log('[Archive Load More] 現在のページ:', currentPage);
        console.log('[Archive Load More] さらに読み込み可能:', data.data.has_more);

        if (!data.data.has_more) {
          console.log('[Archive Load More] 最後のページに到達しました');
          loadMoreBtn.style.display = 'none';
        } else {
          loadMoreBtn.disabled = false;
          loadMoreBtn.textContent = 'もっと見る';
        }
      } else {
        console.error('[Archive Load More] エラー:', data.data?.message || '投稿の取得に失敗しました');
        console.error('[Archive Load More] レスポンス:', data);
        loadMoreBtn.disabled = false;
        loadMoreBtn.textContent = 'もっと見る';
      }
    } catch (error) {
      console.error('[Archive Load More] Ajaxエラー:', error);
      loadMoreBtn.disabled = false;
      loadMoreBtn.textContent = 'もっと見る';
    } finally {
      isLoading = false;
    }
  });
};

initArchiveLoadMore();
