class MobileMenu {
  constructor() {
    this.menu = document.querySelector(".site-header__menu")
    this.openButton = document.querySelector(".site-header__menu-trigger")
    this.body = document.body
    this.events()
  }

  events() {
    this.openButton.addEventListener("click", () => this.openMenu())
  }

  openMenu() {
    this.openButton.classList.toggle("fa-bars")
    this.openButton.classList.toggle("fa-xmark")
    this.menu.classList.toggle("site-header__menu--active")
    this.body.classList.toggle("body--blurred")
  }
}

export default MobileMenu
