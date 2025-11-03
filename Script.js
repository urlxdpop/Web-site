const buttons = document.getElementsByClassName('chooseProduct');
for (let btn of buttons) {
  btn.addEventListener('click', () => {
    window.location.href = './Choose/ChooseProduct.html';
  });
}

import { getProducts } from './Product.js';

let currentProducts = [];
let currentPage = 1;
let totalPages = 1;
let selectedCategory = "Все";

async function loadProducts(page = 1) {
    const data = await getProducts(page);
    currentProducts = data.products;
    currentPage = data.page;
    totalPages = data.pages;
    renderProducts();
}

function renderProducts() {
    const row = document.querySelector('.products-row');
    row.innerHTML = '';

    currentProducts.forEach(product => {
        row.innerHTML += `
            <div class="product-card">
                <img src="${product.mainImage}" alt="${product.title}">
                <h4>${product.title}</h4>
                <div class="product-author">
                    <img src="${product.author.avatar || 'Profile/profile.png'}" alt="Аватар автора">
                    <a href="#" class="author-link" data-author-id="${product.author.id}">${product.author.name}</a>
                </div>
                <h5>Цена: ${product.price} руб.</h5>
                <p>${product.description}</p>
                <button class="chooseProduct" data-id="${product.id}">Посмотреть</button>
            </div>
        `;
    });

    const paginationHtml = `
        <div class="pagination" style="margin-top:20px;text-align:center;">
            ${currentPage > 1 ? `<button onclick="prevPage()">←</button>` : ''}
            <span>Страница ${currentPage} из ${totalPages}</span>
            ${currentPage < totalPages ? `<button onclick="nextPage()">→</button>` : ''}
        </div>
    `;
    row.insertAdjacentHTML('beforeend', paginationHtml);

    document.querySelectorAll('.chooseProduct').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = btn.getAttribute('data-id');
            // Переходим на страницу товара с параметром id
            window.location.href = `./Choose/ChooseProduct.html?id=${encodeURIComponent(id)}`;
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

    const rc = document.getElementById('resultCount');
    if (rc) rc.innerText = `Найдено товаров: ${currentProducts.length}`;
}

window.prevPage = () => {
    if (currentPage > 1) loadProducts(currentPage - 1);
};

window.nextPage = () => {
    if (currentPage < totalPages) loadProducts(currentPage + 1);
};

document.addEventListener('DOMContentLoaded', () => {
    loadProducts();

    const si = document.getElementById('searchInput');
    const ss = document.getElementById('sortSelect');
    if (si) si.addEventListener('input', filterAndSortProducts);
    if (ss) ss.addEventListener('change', filterAndSortProducts);

    document.querySelectorAll('#categoryList li').forEach(li => {
        li.addEventListener('click', () => {
            selectedCategory = li.getAttribute('data-category');
            loadProducts(1);
            document.querySelectorAll('#categoryList li').forEach(el =>
                el.style.color = '');
            li.style.color = '#ed4956';
        });
    });
});