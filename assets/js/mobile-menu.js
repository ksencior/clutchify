const menuBtn = document.getElementById('mobile-menu-btn');
const mobileNavbar = document.querySelector('.mobile-navbar');
let isOpened = false;
menuBtn.addEventListener('click', () => {
    mobileNavbar.classList.toggle('active');
    isOpened = !isOpened;
});
