const buttons = document.getElementsByClassName('chooseProduct');
for (let btn of buttons) {
    btn.addEventListener('click', () => {
        window.location.href = '../Choose/ChooseProduct.html';
    });
}

document.addEventListener('DOMContentLoaded', renderLibrary);

async function renderLibrary() {
    const row = document.getElementById('productsRow');
    if (!row) return;
    row.innerHTML = '';

    try {
        const resp = await fetch('LoadSaved.php', { credentials: 'same-origin' });
        if (!resp.ok) {
            row.innerHTML = '<div style="padding:24px;color:#666;">Ошибка загрузки библиотеки</div>';
            return;
        }
        const data = await resp.json();
        const boughtProducts = data.buy || [];
        const favProducts = data.saved || [];

        row.innerHTML += `<h3>Купленные товары</h3>`;
        if (boughtProducts.length > 0) {
            boughtProducts.forEach(product => {
                row.innerHTML += `
                    <div class="product-card">
                      <img src="../${product.mainImage}" alt="${product.title}">
                      <h4>${product.title}</h4>
                      <h5>Цена: ${product.price} руб.</h5>
                      <p>${product.description}</p>
                      <button class="chooseProduct" data-id="${product.id}">Посмотреть</button>
                    </div>
                `;
            });
            const total = boughtProducts.reduce((sum, p) => sum + (p.price || 0), 0);
            row.innerHTML += `<aside class="sidebar right"><div style="margin:16px 0;font-weight:bold;">Общая сумма: ${total} руб.</div></aside>`;
        } else {
            row.innerHTML += `<div style="padding:12px;color:#666;">Покупок нет</div>`;
        }

        row.innerHTML += `<div class="divider"></div>`;
        row.innerHTML += `<h3>Избранные товары</h3>`;
        if (favProducts.length > 0) {
            favProducts.forEach(product => {
                row.innerHTML += `
                    <div class="product-card">
                      <img src="../${product.mainImage}" alt="${product.title}">
                      <h4>${product.title}</h4>
                      <h5>Цена: ${product.price} руб.</h5>
                      <p>${product.description}</p>
                      <button class="chooseProduct" data-id="${product.id}">Посмотреть</button>
                    </div>
                `;
            });
            const totalFav = favProducts.reduce((sum, p) => sum + (p.price || 0), 0);
            row.innerHTML += `<aside class="sidebar right"><div style="margin:16px 0;font-weight:bold;">Общая сумма: ${totalFav} руб.</div></aside>`;
        } else {
            row.innerHTML += `<div style="padding:12px;color:#666;">Избранных товаров нет</div>`;
        }

        document.querySelectorAll('.chooseProduct').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = btn.getAttribute('data-id');
                window.location.href = `../Choose/ChooseProduct.html?id=${encodeURIComponent(id)}`;
            });
        });

    } catch (err) {
        console.error(err);
        row.innerHTML = '<div style="padding:24px;color:#666;">Ошибка загрузки библиотеки</div>';
    }
}