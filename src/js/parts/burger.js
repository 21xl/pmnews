document.addEventListener('DOMContentLoaded', function() {
    const burger = document.querySelector('.header__burger');
    const header = document.querySelector('.header');
    const logos = document.querySelectorAll('.header__logo-mobile .color');
    const nav = document.querySelector('.header__nav');
    const body = document.querySelector('body');

    burger.addEventListener('click', function() {
        const isActive = burger.classList.toggle('active');
        header.classList.toggle('header__active');
        nav.classList.toggle('active');
        body.classList.toggle('header__freez');

        logos.forEach(function(logo) {
            logo.classList.toggle('active');
        });

        
        if (!isActive) {
            nav.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
});
