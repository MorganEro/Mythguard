class Toast {
    constructor() {
        this.createToastContainer();
        this.activeToasts = new Set();
    }

    createToastContainer() {
        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        document.body.appendChild(this.container);
    }

    show(message, type = 'success') {
        const toastId = `${message}-${type}`;
        if (this.activeToasts.has(toastId)) return;
        
        const toast = document.createElement('div');
        toast.className = `toast toast--${type}`;
        toast.textContent = message;
        
        this.container.appendChild(toast);
        this.activeToasts.add(toastId);

        setTimeout(() => {
            toast.classList.add('toast--fade');
            setTimeout(() => {
                toast.remove();
                this.activeToasts.delete(toastId);
            }, 300);
        }, 3000);
    }

    confirm(message) {
        return new Promise((resolve) => {
            const toast = document.createElement('div');
            toast.className = 'toast toast--confirm';
            
            const messageEl = document.createElement('div');
            messageEl.className = 'toast__message';
            messageEl.textContent = message;
            
            const actions = document.createElement('div');
            actions.className = 'toast__actions';
            
            const confirmBtn = document.createElement('button');
            confirmBtn.className = 'toast__button toast__button--confirm';
            confirmBtn.textContent = 'Confirm';
            
            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'toast__button toast__button--cancel';
            cancelBtn.textContent = 'Cancel';
            
            actions.appendChild(confirmBtn);
            actions.appendChild(cancelBtn);
            toast.appendChild(messageEl);
            toast.appendChild(actions);
            
            this.container.appendChild(toast);
            
            const cleanup = () => {
                toast.classList.add('toast--fade');
                setTimeout(() => toast.remove(), 300);
            };
            
            confirmBtn.addEventListener('click', () => {
                cleanup();
                resolve(true);
            });
            
            cancelBtn.addEventListener('click', () => {
                cleanup();
                resolve(false);
            });
        });
    }
}

export const singletonToast = new Toast();
export default Toast;