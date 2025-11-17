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

export const products = [];

export async function getProducts(page = 1, options = {}) {
  const q = options.q ? encodeURIComponent(options.q) : null;
  const category = options.category ? encodeURIComponent(options.category) : null;
  const sort = options.sort ? encodeURIComponent(options.sort) : null;
  const author = options.author ? encodeURIComponent(options.author) : null;

  const base = new URL('getProducts.php', import.meta.url).href;
  let url = `${base}?page=${page}&limit=20`;
  if (q) url += `&q=${q}`;
  if (category) url += `&category=${category}`;
  if (sort) url += `&sort=${sort}`;
  if (author) url += `&author=${author}`;

  try {
    const response = await fetch(url);
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

getProducts(1).then(data => {
  products.splice(0, products.length, ...data.products);
}).catch(()=>{});