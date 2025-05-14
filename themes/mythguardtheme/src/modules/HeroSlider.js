import Splide from '@splidejs/splide';
import '@splidejs/splide/dist/css/splide.min.css';

class HeroSlider {
  constructor() {
    // Find the slider element
    const sliderElement = document.querySelector('.hero-slider');
    
    if (sliderElement) {
      // Initialize Splide
      const splide = new Splide('.hero-slider', {
        type: 'loop',
        perPage: 1,
        autoplay: true,
        interval: 4000,
        speed: 1000, // transition animation duration in milliseconds
        pauseOnHover: true,
        pagination: true,
        arrows: true,
        classes: {
          pagination: 'splide__pagination hero-slider__bullets',
          page: 'splide__pagination__page hero-slider__bullet'
        }
      });

      // Mount the slider
      splide.mount();
    }
  }
}

export default HeroSlider;
