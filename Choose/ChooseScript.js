const buttons = document.getElementsByClassName('chooseProduct');
for (let btn of buttons) {
  btn.addEventListener('click', () => {
    window.location.href = '../Choose/ChooseProduct.html';
  });
}

import { products } from '../Product.js';

function getQueryId() {
  const params = new URLSearchParams(window.location.search);
  return params.get('id');
}

async function renderProductPage() {
  const id = getQueryId();
  if (!id) {
    window.location.href = '../Main.html';
    return;
  }

  try {
    const response = await fetch(`LoaderInfo.php?id=${encodeURIComponent(id)}`);
    if (!response.ok) throw new Error('Network error');
    const data = await response.json();

    if (data.error) {
      console.error(data.error);
      return;
    }

    const product = data.product;

    // Автор
    document.querySelector('.author-block img').src = '../' + (product.author.avatar || 'Profile/profile.png');
    const authorLink = document.querySelector('.author-name a');
    authorLink.textContent = product.author.name;
    authorLink.onclick = function (e) {
      e.preventDefault();
      localStorage.setItem('selectedAuthorId', product.author.id);
      window.location.href = '../Profile/profile.html';
    };
    document.querySelector('.author-desc').textContent = product.author.desc || '';

    // Информация о товаре
    document.querySelector('.product-title').textContent = product.title;
    document.querySelector('.product-desc').childNodes[0].textContent = product.description;
    document.querySelector('.product-contacts').innerHTML = `
            Контакты для связи: <br>
            Email: ${product.author.email}<br>
        `;

    // Слайдер
    window.images = [product.mainImage, ...(product.images || [])].map(img => '../' + img);
    window.current = 0;
    window.showSlide = function (idx) {
      document.getElementById('sliderImg').src = images[idx];
    };
    window.prevSlide = function () {
      current = (current - 1 + images.length) % images.length;
      showSlide(current);
    };
    window.nextSlide = function () {
      current = (current + 1) % images.length;
      showSlide(current);
    };
    showSlide(current);

    // Похожие товары
    if (data.similar && data.similar.length > 0) {
      document.querySelector('.similar-title').textContent = 'Похожие товары';
      const similarRow = document.querySelector('.similar-row');
      similarRow.innerHTML = '';

      data.similar.forEach(sim => {
        similarRow.innerHTML += `
                    <div class="similar-card">
                        <img src="../${sim.mainImage}" alt="${sim.title}">
                        <h4>${sim.title}</h4>
                        <div class="product-author">
                            <img id="similar-avatar" src="../${sim.author.avatar || 'Profile/profile.png'}" alt="Аватар автора">
                            <a href="#" class="author-link" data-author-id="${sim.author.id}">${sim.author.name}</a>
                        </div>
                        <h5>Цена: ${sim.price} руб.</h5>
                        <p>${sim.description}</p>
                        <button class="chooseProduct" data-id="${sim.id}">Посмотреть</button>
                    </div>
                `;
      });

      document.querySelectorAll('.chooseProduct').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const nid = btn.getAttribute('data-id');
          window.location.href = `ChooseProduct.html?id=${encodeURIComponent(nid)}`;
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

    const buyBtn = document.querySelector('.buyButton');
    if (buyBtn) {
      buyBtn.onclick = async () => {
        try {
          const resp = await fetch('../Save/AddToBuy.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: parseInt(id, 10) })
          });
          const j = await resp.json();
          if (resp.ok && j.success) {
            alert('Товар добавлен в библиотеку');
          } else {
            alert(j.error || 'Ошибка добавления');
          }
        } catch (err) {
          console.error(err);
          alert('Ошибка сети');
        }
      };
    }

    const favBtn = document.querySelector('.favButton');
    if (favBtn) {
      favBtn.onclick = async () => {
        try {
          const resp = await fetch('../Save/AddToSave.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: parseInt(id, 10) })
          });
          const j = await resp.json();
          if (resp.ok && j.success) {
            alert('Товар добавлен в избранное');
          } else {
            alert(j.error || 'Ошибка добавления');
          }
        } catch (err) {
          console.error(err);
          alert('Ошибка сети');
        }
      };
    }

  } catch (err) {
    console.error('Error loading product:', err);
  }
}

document.addEventListener('DOMContentLoaded', renderProductPage);