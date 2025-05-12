class Lightbox {
    constructor() {
        this.initLightbox();
    }

    initLightbox() {
        document.addEventListener('click', (e) => {
            const link = e.target.closest('.guardian-image-link');
            const button = e.target.closest('.lightbox-trigger');
            
            if (link || button) {
                e.preventDefault();
                const imageUrl = link ? link.href : link.closest('.guardian-image').querySelector('.guardian-image-link').href;
                this.openLightbox(imageUrl);
            }
        });
    }

    openLightbox(imageUrl) {
        const image = document.createElement('div');
        image.className = 'mythguard-lightbox-overlay';
        image.innerHTML = `
            <div class="mythguard-lightbox-container">
                <button class="mythguard-lightbox-close" aria-label="Close"><i class="fa-solid fa-square-xmark" aria-hidden="true"></i></button>
                <img src="${imageUrl}" alt="">
            </div>
        `;

        document.body.appendChild(image);
        
       document.body.style.overflow = 'hidden';
        
        setTimeout(() => image.classList.add('is-open'), 10);

        const close = () => {
            image.classList.remove('is-open');
            document.body.style.overflow = '';
            setTimeout(() => image.remove(), 200);
        };

        image.addEventListener('click', (e) => {
            if (e.target === image || e.target.classList.contains('mythguard-lightbox-close') || e.target.closest('.mythguard-lightbox-close')) {
                close();
            }
        });

        document.addEventListener('keyup', function escClose(e) {
            if (e.key === 'Escape') {
                close();
                document.removeEventListener('keyup', escClose);
            }
        });
    }
}

export default Lightbox;
