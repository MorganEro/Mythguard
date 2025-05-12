class FormModal {
  constructor() {
    this.activeElement = null;
    this.mainContent = document.getElementById('main');
    this.bindEvents();
  }

  bindEvents() {
    document.addEventListener('click', this.handleClick.bind(this));
    document.addEventListener('keydown', this.handleKeydown.bind(this));
  }

  handleKeydown(e) {
    if (e.key === 'Escape') {
      const openModal = document.querySelector(
        '.custom-modal[aria-hidden="false"]'
      );
      if (openModal) {
        this.closeModal(openModal);
      }
    }
  }

  openModal(modal) {
    // Store the currently focused element
    this.activeElement = document.activeElement;

    // Set modal attributes
    modal.setAttribute('aria-hidden', 'false');
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');

    // Prevent background scrolling
    document.body.classList.add('body-no-scroll');

    // Make main content inert
    if (this.mainContent) {
      this.mainContent.setAttribute('inert', '');
    }

    // Focus the first focusable element in modal
    setTimeout(() => {
      const input = modal.querySelector('input');
      if (input) {
        input.focus();
      } else {
        const focusable = modal.querySelector(
          'button, [href], input, select, textarea, label, [tabindex]:not([tabindex="-1"])'
        );
        if (focusable) {
          focusable.focus();
        }
      }
    }, 100);
  }

  closeModal(modal) {
    // Reset modal attributes
    modal.setAttribute('aria-hidden', 'true');
    modal.setAttribute('aria-modal', 'false');

    // Remove inert from main content
    if (this.mainContent) {
      this.mainContent.removeAttribute('inert');
    }

    // Re-enable background scrolling
    document.body.classList.remove('body-no-scroll');

    // Restore focus
    if (this.activeElement) {
      this.activeElement.focus();
      this.activeElement = null;
    }
  }

  handleClick(e) {
    const openBtn = e.target.closest('[data-modal-trigger]');
    const closeBtn = e.target.closest('[data-modal-close]');

    if (openBtn) {
      const id = openBtn.getAttribute('data-modal-trigger');
      const modal = document.getElementById(id);
      if (modal) {
        this.openModal(modal);
      }
    }

    if (closeBtn) {
      const modal = closeBtn.closest('.custom-modal');
      if (modal) {
        this.closeModal(modal);
      }
    }
  }
}

export default FormModal;
