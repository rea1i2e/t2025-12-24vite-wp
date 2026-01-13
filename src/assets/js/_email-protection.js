/**
 * メールアドレス保護
 * スパムボット対策として、メールアドレスを分割して表示し、JavaScriptで動的にリンクを生成
 */
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.js-email-protection').forEach(function(el) {
    const emailUser = el.getAttribute('data-email-user');
    const emailDomain = el.getAttribute('data-email-domain');
    const shouldLink = el.getAttribute('data-link') !== 'false';
    
    if (emailUser && emailDomain) {
      const email = emailUser + '@' + emailDomain;
      if (shouldLink) {
        const a = document.createElement('a');
        a.href = 'mailto:' + email;
        a.textContent = email;
        el.appendChild(a);
      } else {
        el.textContent = email;
      }
    }
  });
});
