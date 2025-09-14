const accountBtn = document.getElementById('accountBtn');
if (accountBtn) {
  accountBtn.onclick = function() {
    window.top.location.href = '../Register/Reg.html';
  };
}

const mainBtn = document.getElementById('mainBtn');
if (mainBtn) {
  mainBtn.onclick = function() {
    window.top.location.href = '../Main.html';
  };
}