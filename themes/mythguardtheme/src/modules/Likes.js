import { singletonToast } from "./Toast";

class Likes {
    constructor() {
        this.bindEvents();
    }

    bindEvents() {
        document.addEventListener('click', (e) => {
            const likeBox = e.target.closest('.like-box');
            if (likeBox) {
                this.handleLikeGuardian(likeBox);
            }
        });
    }

    async handleLikeGuardian(likeBox) {
        try {
            if (likeBox.dataset.exists === 'yes') {
                await this.deleteLike(likeBox);
            } else {
                await this.addLike(likeBox);
            }
        } catch (error) {
            singletonToast.show('Error processing like action', 'error');
            console.error('Like error:', error);
        }
    }

    async deleteLike(likeBox) {
        const likeId = likeBox.dataset.like;
        const response = await fetch(`${mythguardData.root_url}/wp-json/mythguard/v1/manageLike`, {
            method: 'DELETE',
            headers: {
                'X-WP-Nonce': mythguardData.nonce,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ like: likeId })
        });

        if (response.ok) {
            likeBox.dataset.exists = 'no';
            const count = likeBox.querySelector('.like-count');
            count.textContent = parseInt(count.textContent) - 1;
        } else {
            throw new Error('Failed to unlike guardian');
        }
    }

    async addLike(likeBox) {
        const guardianId = likeBox.dataset.guardian;
        const response = await fetch(`${mythguardData.root_url}/wp-json/mythguard/v1/manageLike`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': mythguardData.nonce,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ guardianId: guardianId })
        });

        if (response.ok) {
            const data = await response.json();
            likeBox.dataset.exists = 'yes';
            likeBox.dataset.like = data;
            const count = likeBox.querySelector('.like-count');
            count.textContent = parseInt(count.textContent) + 1;
        } else {
            throw new Error('Failed to like guardian');
        }
    }
}

export default Likes;