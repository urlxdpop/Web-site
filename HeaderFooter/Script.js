function getCookie(name) {
  const v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
  return v ? v.pop() : '';
}

const accountBtn = document.getElementById('accountBtn');

if (accountBtn) {
  accountBtn.onclick = function() {
    // читаем cookie в момент клика (не один раз при загрузке)
    console.log('User ID from cookie:', getCookie('user_id'));
    if (getCookie('user_id')) {
      window.top.location.href = '../Profile/MyProfile.html';
    } else {
      window.top.location.href = '../Register/Reg.html';
    }
  };
}

const mainBtn = document.getElementById('mainBtn');
if (mainBtn) {
  mainBtn.onclick = function() {
    window.top.location.href = '../Main.html';
  };
}

const saveBtn = document.getElementById('Save');
if (saveBtn) {
  saveBtn.onclick = function() {
    window.top.location.href = '../Save/Saved.html';
  };
}