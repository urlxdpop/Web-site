// minimal script: проверка авторизации и заполнение поля author
document.addEventListener('DOMContentLoaded', () => {
  function getCookie(name) {
    const v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
    return v ? v.pop() : '';
  }
  const uid = getCookie('user_id');
  const authorInput = document.getElementById('authorInput');
  if (!uid || uid === '0') {
    // не авторизован — редирект на страницу входа
    window.location.href = '../Register/Login.html';
    return;
  }
  if (authorInput) authorInput.value = uid;

  const cancelBtn = document.getElementById('cancelBtn');
  if (cancelBtn) cancelBtn.addEventListener('click', () => window.top.location.href = '../Main.html');
});