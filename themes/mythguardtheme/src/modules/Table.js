class Table {
  constructor() {
    this.init();
  }

  init() {
    this.addMobileLabels();
  }

  addMobileLabels() {
    document.querySelectorAll(".guardian-roster tbody tr").forEach(row => {
      const headers = ["Guardian Type", "Guardian Description", "Quantity"];
      row.querySelectorAll("td").forEach((cell, i) => {
        cell.setAttribute("data-label", headers[i]);
      });
    });
  }
}

export default Table;
