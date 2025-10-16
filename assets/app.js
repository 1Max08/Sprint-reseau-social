import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

/* Dark mode */
document.querySelector('#theme').addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
});
/*Menu burger*/
const menuBtn = document.getElementById('menu-btn');
const mobileMenu = document.getElementById('mobile-menu');

if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', () => {
        const expanded = menuBtn.getAttribute('aria-expanded') === 'true';
        menuBtn.setAttribute('aria-expanded', String(!expanded));
        mobileMenu.classList.toggle('hidden');
    });
}
