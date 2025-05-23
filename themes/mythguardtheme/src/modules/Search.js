import Spinner from './Spinner';

class Search {
  constructor() {
    this.initializeProperties();
    this.injectHtml();
    this.initializeElements();
    this.bindEvents();
  }

  // Initialize class properties
  initializeProperties() {
    this.isOverlayOpen = false;
    this.typingTimer = null;
    this.isSpinnerVisible = false;
  }

  // Get DOM elements
  initializeElements() {
    this.searchTriggers = document.querySelectorAll('.js-search-trigger');
    this.searchOverlay = document.querySelector('.search-overlay');
    this.closeButton = document.querySelector('.search-overlay__close');
    this.searchInput = document.querySelector('.search-term');
    this.searchResults = document.querySelector('.search-overlay__results');
  }

  /**
   * Binds event listeners for search functionality.
   * Implements progressive enhancement:
   * - With JS: Opens search overlay
   * - Without JS: Falls back to dedicated search page
   */
  bindEvents() {
    // Click events
    this.searchTriggers.forEach(trigger => {
      trigger.addEventListener('click', e => {
        e.preventDefault();
        this.openOverlay();
      });
    });

    if (this.closeButton) {
      this.closeButton.addEventListener('click', () => this.closeOverlay());
    }

    // Keyboard events
    document.addEventListener('keyup', e => this.handleKeyPress(e));
    this.searchInput.addEventListener('input', () => this.handleSearchInput());
  }

  // Handle keyboard events
  handleKeyPress(e) {
    const isTypingInField = this.isUserTypingInField();

    if (e.key === 's' && !this.isOverlayOpen && !isTypingInField) {
      this.openOverlay();
    }
    if (e.key === 'Escape' && this.isOverlayOpen) {
      this.closeOverlay();
    }
  }

  // Check if user is typing in a form field
  isUserTypingInField() {
    return (
      ['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName) ||
      document.activeElement.hasAttribute('contenteditable')
    );
  }

  // Handle search input changes
  handleSearchInput() {
    clearTimeout(this.typingTimer);

    if (!this.searchInput.value) {
      this.clearSearchResults();
      return;
    }

    this.showLoadingSpinner();
    this.scheduleSearch();
  }

  // UI Methods
  injectHtml() {
    document.body.insertAdjacentHTML(
      'beforeend',
      `
      <div class="search-overlay">
        <div class="search-overlay__top">
          <div class="container search-overlay__top-inner">
            <i class="fa-solid fa-magnifying-glass search-overlay__icon" aria-hidden="true"></i>
            <input type="text" class="search-term" placeholder="What do you seek?" id="search-term">
            <i class="fa-solid fa-square-xmark search-overlay__close" aria-hidden="true"></i>
          </div>
        </div>
        <div class="container">
          <div class="search-overlay__results"></div>
        </div>
      </div>
    `
    );
  }

  openOverlay() {
    this.searchOverlay.classList.add('search-overlay--active');
    document.body.classList.add('body-no-scroll');
    this.isOverlayOpen = true;
    setTimeout(() => this.searchInput.focus(), 300);
  }

  closeOverlay() {
    this.searchOverlay.classList.remove('search-overlay--active');
    document.body.classList.remove('body-no-scroll');
    this.isOverlayOpen = false;
    this.searchInput.value = '';
    this.clearSearchResults();
  }

  showLoadingSpinner() {
    if (!this.isSpinnerVisible) {
      this.searchResults.innerHTML = Spinner.render();
      this.isSpinnerVisible = true;
    }
  }

  clearSearchResults() {
    this.searchResults.innerHTML = '';
    this.isSpinnerVisible = false;
  }

  // Search Methods
  scheduleSearch() {
    this.typingTimer = setTimeout(() => this.getResults(), 750);
  }

  async getResults() {
    try {
      const data = await wp.apiFetch({
        path: `/mythguard/v1/search?term=${this.searchInput.value}`,
      });
      this.renderSearchResults(data);
    } catch (e) {
      console.error('Search error:', e);
      this.handleSearchError();
    }
  }

  handleSearchError() {
    this.searchResults.innerHTML = '<p>Unexpected error. Please try again.</p>';
    this.isSpinnerVisible = false;
  }

  getEventTypeHtml(items) {
    return `
    ${items
      .map(
        item => `
        <div class="gathering-summary">
          <a class="gathering-summary__date t-center" href=" ${item.permalink}">
        <span class="gathering-summary__month">${item.month}</span>
        <span class="gathering-summary__day">
           ${item.day}
        </span>
    </a>
    <div class="gathering-summary__content">
        <h5 class="gathering-summary__title headline headline--tiny"><a href="${item.permalink}">${item.title}</a></h5>
        <p>${item.description} <a href="${item.permalink}" class="nu gray">Learn more</a></p>
            </div>
        </div>`
      )
      .join('')}`;
  }

  getGuardianTypeHtml(items) {
    return `
    <ul class="guardian-cards">
      ${items
        .map(
          item => `
        <li class="guardian-card__list-item">
                    <a class="guardian-card" href="${item.permalink}">
                        <img class="guardian-card__image" src="${item.image}">
                        <span class="guardian-card__name">${item.title}</span>
                    </a>
                </li>
      `
        )
        .join('')}
    </ul>`;
  }

  getGenericTypeHtml(items) {
    return `
      <ul class="link-list min-list">
        ${items
          .map(item => {
            const authorName =
              item.postType == 'post'
                ? ` <small>by ${item.authorName}</small>`
                : '';
            return `<li><a href="${item.permalink}">${item.title}</a>${authorName}</li>`;
          })
          .join('')}
      </ul>
    `;
  }

  renderSearchResults(data) {
    this.isSpinnerVisible = false;
    const { generalInfo, guardians, programs, locations, gatherings } = data;

    if (
      !generalInfo.length &&
      !guardians.length &&
      !programs.length &&
      !locations.length &&
      !gatherings.length
    ) {
      this.searchResults.innerHTML =
        '<h2 class="search-overlay__section-title">No results found</h2>';
      return;
    }

    this.searchResults.innerHTML = `
      <div class='row'>
        <div class="one-third">
          <h2 class="search-overlay__section-title">General Information</h2>
          ${generalInfo.length ? this.getGenericTypeHtml(generalInfo) : ''}
        </div>
        <div class="one-third">
          <h2 class="search-overlay__section-title">Programs</h2>
          ${programs.length ? this.getGenericTypeHtml(programs) : ''}
          <h2 class="search-overlay__section-title">Guardians</h2>
          ${guardians.length ? this.getGuardianTypeHtml(guardians) : ''}
        </div>
        <div class="one-third">
          <h2 class="search-overlay__section-title">Locations</h2>
          ${locations.length ? this.getGenericTypeHtml(locations) : ''}
          <h2 class="search-overlay__section-title">Gatherings</h2>
          ${gatherings.length ? this.getEventTypeHtml(gatherings) : ''}
        </div>
      </div>
    `;
  }
}

export default Search;
