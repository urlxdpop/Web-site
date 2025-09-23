import { authors, products } from '../Product.js';

function renderProfile() {
  const authorId = localStorage.getItem('selectedAuthorId') || authors[0].id;
  const author = authors.find(a => a.id == authorId) || authors[0];

  document.querySelector('.profile-main-avatar').src = author.avatar;
  document.querySelector('.profile-name').textContent = author.name;
  document.querySelector('.profile-desc').textContent = author.desc;
  document.querySelector('.profile-contacts').innerHTML = `
    Email: ${author.contacts.email}<br>
    Telegram: ${author.contacts.telegram}<br>
    Телефон: ${author.contacts.phone}
  `;

  const row = document.querySelector('.products-row');
  row.innerHTML = '';
  products.filter(p => p.author.id == author.id).forEach(product => {
    row.innerHTML += `
      <div class="product-card">
        <img src="../${product.mainImage}" alt="${product.title}">
        <h4>${product.title}</h4>
        <div class="product-author">
          <img src="../${product.author.avatar}" alt="Аватар автора">
          <a href="#" class="author-link" data-author-id="${product.author.id}">${product.author.name}</a>
        </div>
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
      window.location.reload();
    });
  });
}

document.addEventListener('DOMContentLoaded', renderProfile);