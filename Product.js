class Author {
  constructor({ id, name, desc, avatar }) {
    this.id = id;
    this.name = name;
    this.desc = desc;
    this.avatar = avatar;
  }
}

class Product {
  constructor({ id, title, mainImage, type, description, price, author, images }) {
    this.id = id;
    this.title = title;
    this.mainImage = mainImage;
    this.type = type;
    this.price = price;
    this.description = description;
    this.author = new Author(author);
    this.images = images || [];
  }
}

// экспортируем кэш продуктов (чтобы другие модули могли импортировать)
export const products = [];

export async function getProducts(page = 1) {
  try {
    const response = await fetch(`getProducts.php?page=${page}&limit=20`);
    if (!response.ok) throw new Error('Network error');
    const data = await response.json();
    return {
      products: data.products.map(p => new Product(p)),
      total: data.total,
      page: data.page,
      pages: data.pages
    };
  } catch (err) {
    console.error('Error loading products:', err);
    return { products: [], total: 0, page: 1, pages: 1 };
  }
}

// сразу загружаем первую страницу в кэш (без блокировки)
getProducts(1).then(data => {
  products.splice(0, products.length, ...data.products);
}).catch(()=>{});