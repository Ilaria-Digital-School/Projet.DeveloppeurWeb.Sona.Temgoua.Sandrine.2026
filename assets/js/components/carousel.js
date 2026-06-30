// assets/js/components/carousel.js
import { Carousel } from 'bootstrap';

export function initCarousel() {
    const heroCarousel = document.querySelector('#carouselPromo');
    if (heroCarousel) {
        new Carousel(heroCarousel, {
            interval: 12000,
            ride: 'carousel',
            pause: false,
            wrap: true
        });
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', initCarousel);