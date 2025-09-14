const images = [
    "boom0.png",
    "boom1.png",
    "boom2.png",
    "boom3.png",
    "boom4.png",
    "boom5.png",
];
let current = 0;
function showSlide(idx) {
    document.getElementById('sliderImg').src = images[idx];
}
function prevSlide() {
    current = (current - 1 + images.length) % images.length;
    showSlide(current);
}
function nextSlide() {
    current = (current + 1) % images.length;
    showSlide(current);
}


const buttons = document.getElementsByClassName('chooseProduct');
for (let btn of buttons) {
  btn.addEventListener('click', () => {
    window.location.href = '../Choose/ChooseProduct.html';
  });
}