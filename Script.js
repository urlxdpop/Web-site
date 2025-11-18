const buttons = document.getElementsByClassName('chooseProduct');
for (let btn of buttons) {
  btn.addEventListener('click', () => {
    window.location.href = './Choose/ChooseProduct.html';
  });
}

import { getProducts } from './Product.js';

let allProducts = [];
let visibleProducts = [];
let currentPage = 1;
let totalPages = 1;
let selectedCategory = "Все";
let searchTerm = '';
let debounceTimer = null;
let currentSort = null;

async function loadProducts(page = 1) {
    const opts = {
        q: searchTerm || null,
        category: selectedCategory && selectedCategory !== 'Все' ? selectedCategory : null,
        sort: currentSort || null
    };
    const data = await getProducts(page, opts);
    allProducts = data.products;
    currentPage = data.page;
    totalPages = data.pages;
    applyFiltersAndRender(); 
}

function applyFiltersAndRender() {

    visibleProducts = allProducts.slice();
    renderProducts();
}

function renderProducts() {
    const row = document.querySelector('.products-row');
    if (!row) return;
    row.innerHTML = '';

    const listToShow = visibleProducts;
    if (listToShow.length === 0) {
        row.innerHTML = '<div class="no-results">Товары не найдены</div>';
    } else {
        listToShow.forEach(product => {
            row.innerHTML += `
                <div class="product-card">
                    <img src="${product.mainImage ? ('../WebSite/' + product.mainImage) : 'Profile/profile.png'}" alt="${product.title}">
                    <h4>${product.title}</h4>
                    <div class="product-author">
                        <img src="${product.author && product.author.avatar ? ('../WebSite/' + product.author.avatar) : 'Profile/profile.png'}" alt="Аватар автора">
                        <a href="#" class="author-link" data-author-id="${product.author && product.author.id ? product.author.id : ''}">${product.author && product.author.name ? product.author.name : 'Автор'}</a>
                    </div>
                    <h5>Цена: ${product.price} руб.</h5>
                    <p>${product.description}</p>
                    <button class="chooseProduct" data-id="${product.id}">Посмотреть</button>
                </div>
            `;
        });
    }

    const paginationHtml = `
        <div class="pagination">
            ${currentPage > 1 ? `<button onclick="prevPage()"><</button>` : ''}
            <span>Страница ${currentPage} из ${totalPages}</span>
            ${currentPage < totalPages ? `<button onclick="nextPage()">></button>` : ''}
        </div>
    `;
    row.insertAdjacentHTML('beforeend', paginationHtml);

    document.querySelectorAll('.chooseProduct').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = btn.getAttribute('data-id');
            window.location.href = `./Choose/ChooseProduct.html?id=${encodeURIComponent(id)}`;
        });
    });

    document.querySelectorAll('.author-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const authorId = link.getAttribute('data-author-id');
            if (authorId) {
                localStorage.setItem('selectedAuthorId', authorId);
                window.location.href = './Profile/profile.html';
            }
        });
    });

    const rc = document.getElementById('resultCount');
    if (rc) rc.innerText = `Найдено товаров: ${visibleProducts.length}`;
}

window.prevPage = () => {
    if (currentPage > 1) loadProducts(currentPage - 1);
};

window.nextPage = () => {
    if (currentPage < totalPages) loadProducts(currentPage + 1);
};

function debounce(fn, ms = 300) {
    return (...args) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fn(...args), ms);
    };
}

function filterAndSortProducts() {
    const si = document.getElementById('searchInput');
    const ss = document.getElementById('sortSelect');
    searchTerm = si ? si.value.trim() : '';
    currentSort = ss ? ss.value : null;
    loadProducts(1);
}

document.addEventListener('DOMContentLoaded', () => {
    loadProducts();

    const si = document.getElementById('searchInput');
    const ss = document.getElementById('sortSelect');
    const sbtn = document.getElementById('searchBtn');
    if (si) si.addEventListener('input', debounce(filterAndSortProducts, 300));
    if (sbtn) sbtn.addEventListener('click', () => filterAndSortProducts());
    if (ss) ss.addEventListener('change', filterAndSortProducts);

    document.querySelectorAll('#categoryList li').forEach(li => {
        li.addEventListener('click', () => {
            selectedCategory = li.getAttribute('data-category') || 'Все';
            document.querySelectorAll('#categoryList li').forEach(el => el.classList.remove('active'));
            li.classList.add('active');
            loadProducts(1);
        });
    });
});