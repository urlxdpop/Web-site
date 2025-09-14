

const buttons = document.getElementsByClassName('chooseProduct');
for (let btn of buttons) {
  btn.addEventListener('click', () => {
    window.location.href = '../Choose/ChooseProduct.html';
  });
}