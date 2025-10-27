class Author {
  constructor({ id, name, desc, avatar, contacts }) {
    this.id = id;
    this.name = name;
    this.desc = desc;
    this.avatar = avatar;
    this.contacts = contacts;
  }
}

class Product {
  constructor({ id, title, mainImage, type, description, author, images, similar }) {
    this.id = id;
    this.title = title;
    this.mainImage = mainImage;
    this.type = type;
    this.price = Math.floor(Math.random() * 1000); // Random price for demonstration
    this.description = description;
    this.author = author; // Author instance
    this.images = images;
    this.similar = similar;
    this.buy = false;
  }
}

export const authors = [
  new Author({
    id: 1,
    name: "Nia",
    desc: "Описание автора",
    avatar: "#",
    contacts: { email: "em", telegram: "@wdas", phone: "911" }
  }),
  new Author({
    id: 2,
    name: "Killjoy",
    desc: "Описание автора",
    avatar: "#",
    contacts: { email: "em", telegram: "@wdas", phone: "911" }
  })
];

export const products = [
  new Product({
    id: 1,
    title: "Норм игра",
    mainImage: "UI0.png",
    type: "Игры",
    description: "Описание товара",
    author: authors[0],
    images: ["boom0.png", "boom1.png", "boom2.png", "boom3.png", "boom4.png", "boom5.png"],
    similar: [2] 
  }),
  new Product({
    id: 2,
    title: "Топ игра",
    mainImage: "UI0.png",
    type: "Игры",
    description: "Описание товара",
    author: authors[0],
    images: ["boom0.png", "boom1.png", "boom2.png", "boom3.png", "boom4.png", "boom5.png"],
    similar: [ 1] 
  }),
  new Product({
    id: 3,
    title: "Топ приложуха",
    mainImage: "UI1.png",
    type: "Приложение",
    description: "Описание товара",
    author: authors[0],
    images: ["boom0.png", "boom1.png", "boom2.png", "boom3.png", "boom4.png", "boom5.png"],
    similar: [4,5] 
  }),
  new Product({
    id: 4,
    title: "Норм приложуха",
    mainImage: "UI1.png",
    type: "Приложение",
    description: "Тип очень помогает в жизни",
    author: authors[1],
    images: ["boom0.png", "boom1.png", "boom2.png", "boom3.png", "boom4.png", "boom5.png"],
    similar: [3,5] 
  }),
  new Product({
    id: 5,
    title: "Settings app",
    mainImage: "UI1.png",
    type: "Приложение",
    description: "Описание товара",
    author: authors[0],
    images: ["boom0.png", "boom1.png", "boom2.png", "boom3.png", "boom4.png", "boom5.png"],
    similar: [3,4] 
  }),
  new Product({
    id: 6,
    title: "Топ книга",
    mainImage: "UI2.png",
    type: "Книги",
    description: "Описание товара",
    author: authors[0],
    images: ["boom0.png", "boom1.png", "boom2.png", "boom3.png", "boom4.png", "boom5.png"],
    similar: [7] 
  }),
];