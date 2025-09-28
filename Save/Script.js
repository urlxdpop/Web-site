const buttons = document.getElementsByClassName('chooseProduct');
for (let btn of buttons) {
    btn.addEventListener('click', () => {
        window.location.href = '../Choose/ChooseProduct.html';
    });
}

import { products } from '../Product.js';


function renderLibrary() {
    const boughtIds = JSON.parse(localStorage.getItem('boughtProducts') || '[]');
    const favIds = JSON.parse(localStorage.getItem('favProducts') || '[]');

    const boughtProducts = products.filter(p => boughtIds.includes(String(p.id)));
    const favProducts = products.filter(p => favIds.includes(String(p.id)) && !boughtIds.includes(String(p.id)));

    const row = document.getElementById('productsRow');
    row.innerHTML = '';

    // Купленные товары
    row.innerHTML += `<h3>Купленные товары</h3>`;
    if (boughtProducts.length > 0) {
        boughtProducts.forEach(product => {
            row.innerHTML += `
        <div class="product-card">
          <img src="../${product.mainImage}" alt="${product.title}">
          <h4>${product.title}</h4>
          <h5>Цена: ${product.price} руб.</h5>
          <p>${product.description}</p>
        </div>
      `;
        });
        const total = boughtProducts.reduce((sum, p) => sum + p.price, 0);
        row.innerHTML += `<aside class="sidebar right">
        <div style="margin:16px 0;font-weight:bold;">Общая сумма: ${total} руб.</div>
        <hr style="margin:24px 0;"
        </aside>`;
    }

    // Избранные товары
    row.innerHTML += `<h3>Избранные товары</h3>`;
    if (favProducts.length > 0) {

        favProducts.forEach(product => {
            row.innerHTML += `
        <div class="product-card">
          <img src="../${product.mainImage}" alt="${product.title}">
          <h4>${product.title}</h4>
          <h5>Цена: ${product.price} руб.</h5>
          <p>${product.description}</p>
          <button class="chooseProduct" data-id="${product.id}">Купить</button>
        </div>
      `;
        });
        const total = favProducts.reduce((sum, p) => sum + p.price, 0);
        row.innerHTML += `<aside class="sidebar right">
        <div style="margin:16px 0;font-weight:bold;">Общая сумма: ${total} руб.</div>
        <hr style="margin:24px 0;"
        </aside>`;
    }

    document.querySelectorAll('.chooseProduct').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = btn.getAttribute('data-id');
            localStorage.setItem('selectedProductId', id);
            window.location.href = '../Choose/ChooseProduct.html';
        });
    });
}

document.addEventListener('DOMContentLoaded', renderLibrary);