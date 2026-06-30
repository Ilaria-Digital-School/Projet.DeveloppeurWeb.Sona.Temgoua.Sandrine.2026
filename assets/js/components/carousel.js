// assets/js/components/carousel.js

import { Carousel } from 'bootstrap';

function initCarousel() {
    const heroCarousel = document.querySelector('#carouselPromo');

    if (!heroCarousel) {
        return;
    }

    new Carousel(heroCarousel, {
        interval: 12000,
        ride: 'carousel',
        pause: false,
        wrap: true
    });
}

document.addEventListener('DOMContentLoaded', initCarousel);