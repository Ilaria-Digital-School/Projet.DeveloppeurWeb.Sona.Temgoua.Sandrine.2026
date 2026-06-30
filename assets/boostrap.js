// assets/bootstrap.js
import { Carousel } from 'bootstrap';

// Configuration globale de Bootstrap
document.addEventListener('DOMContentLoaded', () => {
    // Carousel principal
    const heroCarousel = document.querySelector('#carouselPromo');
    if (heroCarousel) {
        new Carousel(heroCarousel, {
            interval: 12000,
            ride: 'carousel',
            pause: false,
            wrap: true
        });
    }
});