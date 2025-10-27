import { products } from '../Product.js';

function getCookie(name) {
  const v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
  return v ? v.pop() : '';
}

async function renderMyProfile() {
  const selectedAuthorId = localStorage.getItem('selectedAuthorId');
  const url = selectedAuthorId ? `fileDB.php?id=${encodeURIComponent(selectedAuthorId)}` : 'fileDB.php';
  let resp;
  try {
    // отправляем куки вместе с запросом
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
    products.filter(p => p.author && p.author.id == authorId).forEach(product => {
      row.innerHTML += `
        <div class="product-card">
          <img src="../${product.mainImage}" alt="${product.title}">
          <h4>${product.title}</h4>
          <div class="product-author">
            <img src="../${product.author.avatar}" alt="Аватар автора">
            <a href="#" class="author-link" data-author-id="${product.author.id}">${product.author.name}</a>
          </div>
          <h5>Цена: ${product.price} руб.</h5>
          <p>${product.description}</p>
          <button class="chooseProduct" data-id="${product.id}">Купить</button>
        </div>
      `;
    });

    document.querySelectorAll('.chooseProduct').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = btn.getAttribute('data-id');
        localStorage.setItem('selectedProductId', id);
        window.location.href = '../Choose/ChooseProduct.html';
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

const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
  logoutBtn.addEventListener('click', () => {
    // удалить cookie user_id и перенаправить на главную
    document.cookie = 'user_id=; Max-Age=0; path=/';
    window.top.location.href = '../Main.html';
  });
}