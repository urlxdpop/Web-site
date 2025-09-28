const buttons = document.getElementsByClassName('chooseProduct');
for (let btn of buttons) {
  btn.addEventListener('click', () => {
    window.location.href = './Choose/ChooseProduct.html';
  });
}

import { products } from './Product.js';

let filteredProducts = [...products];
let selectedCategory = "Все";

function renderProducts() {
  const row = document.querySelector('.products-row');
  row.innerHTML = '';
  filteredProducts.forEach(product => {
    row.innerHTML += `
      <div class="product-card">
        <img src="${product.mainImage}" alt="${product.title}">
        <h4>${product.title}</h4>
        <div class="product-author">
          <img src="${product.author.avatar}" alt="Аватар автора">
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
      window.location.href = './Choose/ChooseProduct.html';
    });
  });

  document.querySelectorAll('.author-link').forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const authorId = link.getAttribute('data-author-id');
      localStorage.setItem('selectedAuthorId', authorId);
      window.location.href = './Profile/profile.html';
    });
  });

  document.getElementById('resultCount').innerText = `Найдено товаров: ${filteredProducts.length}`;
}

function filterAndSortProducts() {
  const searchValue = document.getElementById('searchInput').value.toLowerCase();
  const sortValue = document.getElementById('sortSelect').value;

  filteredProducts = products.filter(product => {
    const matchesCategory = selectedCategory === "Все" || product.type === selectedCategory;
    const matchesSearch = product.title.toLowerCase().includes(searchValue) ||
      product.description.toLowerCase().includes(searchValue) ||
      product.author.name.toLowerCase().includes(searchValue);
    return matchesCategory && matchesSearch;
  });

  filteredProducts.sort((a, b) => {
    if (sortValue === 'title') {
      return a.title.localeCompare(b.title);
    }
    if (sortValue === 'author') {
      return a.author.name.localeCompare(b.author.name);
    }
    return 0;
  });

  renderProducts();
}

document.addEventListener('DOMContentLoaded', () => {
  renderProducts();
  document.getElementById('searchInput').addEventListener('input', filterAndSortProducts);
  document.getElementById('sortSelect').addEventListener('change', filterAndSortProducts);

  document.querySelectorAll('#categoryList li').forEach(li => {
    li.addEventListener('click', () => {
      selectedCategory = li.getAttribute('data-category');
      filterAndSortProducts();
      document.querySelectorAll('#categoryList li').forEach(el => el.style.color = '');
      li.style.color = '#ed4956';
    });
  });
});