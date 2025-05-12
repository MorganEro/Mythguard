class Spinner {
  constructor() {
    if (document.querySelector('.spinner-container')) {
      this.injectHTML();
    }
  }

  getImageUrl(filename) {
    return `${mythguardData.root_url}/wp-content/themes/mythguardtheme/images/spinner/${filename}`;
  }

  injectHTML() {
    const containers = document.querySelectorAll('.spinner-container');
    containers.forEach(container => {
      container.innerHTML = this.getSpinnerHTML();
    });
  }

  getSpinnerHTML() {
    return `
      <div class="spinner-loader">
        <div class="shield">
          <img src="${this.getImageUrl('mythguard-shield.svg')}" alt="Loading..." />
        </div>
        <div class="emblem">
          <img src="${this.getImageUrl('mythguard-emblem.svg')}" alt="Loading..." />
        </div>
      </div>
    `;
  }

  static render() {
    return new Spinner().getSpinnerHTML();
  }
}

export default Spinner;
