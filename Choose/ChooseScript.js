const buttons = document.getElementsByClassName('chooseProduct');
for (let btn of buttons) {
  btn.addEventListener('click', () => {
    window.location.href = '../Choose/ChooseProduct.html';
  });
}

import { products } from '../Product.js';

function renderProductPage() {
  const id = localStorage.getItem('selectedProductId');
  const product = products.find(p => p.id == id) || products[0];

  // Автор
  document.querySelector('.author-block img').src = '../' + product.author.avatar;
  const authorLink = document.querySelector('.author-name a');
  authorLink.textContent = product.author.name;
  authorLink.href = '../Profile/profile.html';
  authorLink.onclick = function(e) {
    e.preventDefault();
    localStorage.setItem('selectedAuthorId', product.author.id);
    window.location.href = '../Profile/profile.html';
  };
  document.querySelector('.author-desc').textContent = product.author.desc;

  // Информация о товаре
  document.querySelector('.product-title').textContent = product.title;
  document.querySelector('.product-desc').childNodes[0].textContent = product.description;
  document.querySelector('.product-contacts').innerHTML = `
    Контакты для связи: <br>
    Email: ${product.author.contacts.email}<br>
    Телефон: ${product.author.contacts.phone}
  `;

  // Слайдер
  window.images = product.images.map(img => img.startsWith('boom') ? img : '../' + img);
  window.current = 0;
  window.showSlide = function(idx) {
    document.getElementById('sliderImg').src = images[idx];
  };
  window.prevSlide = function() {
    current = (current - 1 + images.length) % images.length;
    showSlide(current);
  };
  window.nextSlide = function() {
    current = (current + 1) % images.length;
    showSlide(current);
  };
  showSlide(current);

  // Похожие товары
  document.querySelector('.similar-title').textContent = 'Похожие товары';
  const similarRow = document.querySelector('.similar-row');
  similarRow.innerHTML = '';
  product.similar.forEach(simId => {
    const sim = products.find(p => p.id == simId);
    if (!sim) return;
    similarRow.innerHTML += `
      <div class="similar-card">
        <img src="../${sim.mainImage}" alt="${sim.title}">
        <h4>${sim.title}</h4>
        <div class="product-author">
          <img id="similar-avatar" src="../${sim.author.avatar}" alt="Аватар автора">
          <a href="../Profile/profile.html" class="author-link" data-author-id="${sim.author.id}">${sim.author.name}</a>
        </div>
        <p>${sim.description}</p>
        <button class="chooseProduct" data-id="${sim.id}">Купить</button>
      </div>
    `;
  });

  document.querySelectorAll('.chooseProduct').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const id = btn.getAttribute('data-id');
      localStorage.setItem('selectedProductId', id);
      window.location.reload();
    });
  });
  document.querySelectorAll('.author-link').forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const authorId = link.getAttribute('data-author-id');
      localStorage.setItem('selectedAuthorId', authorId);
      window.location.href = '../Profile/profile.html';
    });
  });
}

document.addEventListener('DOMContentLoaded', renderProductPage);