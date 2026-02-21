// assets/app.js
import './styles/app.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import { Carousel } from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const heroCarousel = document.querySelector('#carouselPromo');

    if (heroCarousel) {
        new Carousel(heroCarousel, {
            interval: 4000,   // 4 secondes entre chaque slide
            ride: 'carousel',
            pause: false,     // continue même au hover
            wrap: true        // boucle infinie
        });
    }
});

