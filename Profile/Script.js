function getCookie(name) {
  const v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
  return v ? v.pop() : '';
}

async function renderMyProfile() {
  const selectedAuthorId = localStorage.getItem('selectedAuthorId');
  const url = selectedAuthorId ? `fileDB.php?id=${encodeURIComponent(selectedAuthorId)}` : 'fileDB.php';
  let resp;
  try {
    resp = await fetch(url, { credentials: 'same-origin' });
    if (!resp.ok) {
      console.error('fileDB http error', resp.status);
      return;
    }
    const json = await resp.json();
    if (json.error) {
      console.error('fileDB error', json);
      return;
    }
    const author = json;

    document.querySelector('.profile-main-avatar').src = author.avatar ? ('../' + author.avatar) : '../Profile/profile.png';
    document.querySelector('.profile-name').textContent = author.username || 'Пользователь';
    document.querySelector('.profile-desc').textContent = author.descr || '';
    document.querySelector('.profile-contacts').innerHTML = `
      Email: ${author.email || ''}<br>
    `;

    const row = document.querySelector('.products-row');
    row.innerHTML = '';

    const authorId = author.id;
    // загружаем товары автора через API
    try {
      const resp2 = await fetch(`../getProducts.php?author=${encodeURIComponent(authorId)}&limit=50`);
      if (resp2.ok) {
        const data2 = await resp2.json();
        (data2.products || []).forEach(product => {
          row.innerHTML += `
            <div class="product-card">
              <img src="../${product.mainImage}" alt="${product.title}">
              <h4>${product.title}</h4>
              <div class="product-author">
                <img src="../${product.author.avatar || 'Profile/profile.png'}" alt="Аватар автора">
                <a href="#" class="author-link" data-author-id="${product.author.id}">${product.author.name}</a>
              </div>
              <h5>Цена: ${product.price} руб.</h5>
              <p>${product.description}</p>
              <button class="chooseProduct" data-id="${product.id}">Посмотреть</button>
            </div>
          `;
        });
      } else {
        console.warn('getProducts by author failed', resp2.status);
      }
    } catch (err) {
      console.error('Ошибка загрузки товаров автора', err);
    }

    document.querySelectorAll('.chooseProduct').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = btn.getAttribute('data-id');
        window.location.href = `../Choose/ChooseProduct.html?id=${encodeURIComponent(id)}`;
      });
    });

    document.querySelectorAll('.author-link').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const authorId = link.getAttribute('data-author-id');
        localStorage.setItem('selectedAuthorId', authorId);
        window.location.href = './profile.html';
      });
    });

  } catch (err) {
    console.error('Ошибка получения данных профиля', err);
  }
}

document.addEventListener('DOMContentLoaded', renderMyProfile);

// добавляем логику выхода и создания товара
const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
  logoutBtn.addEventListener('click', () => {
    document.cookie = 'user_id=; Max-Age=0; path=/';
    window.top.location.href = '../Main.html';
  });
}

const createBtn = document.getElementById('createProductBtn');
if (createBtn) {
  createBtn.addEventListener('click', () => {
    // проверяем cookie и перенаправляем
    const v = document.cookie.match('(^|;)\\s*user_id\\s*=\\s*([^;]+)');
    const uid = v ? v.pop() : null;
    if (!uid || uid === '0') {
      window.top.location.href = '../Register/Login.html';
    } else {
      window.location.href = '../createContent/CreatorForm.html';
    }
  });
}