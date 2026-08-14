const btnSale = document.getElementById("btnSale");
const btnRent = document.getElementById("btnRent");

const saleSection = document.getElementById("saleProperties");
const rentSection = document.getElementById("rentProperties");

btnSale.addEventListener("click", () => {
  btnSale.classList.add("active");
  btnRent.classList.remove("active");

  saleSection.classList.remove("hidden");
  rentSection.classList.add("hidden");
});

btnRent.addEventListener("click", () => {
  btnRent.classList.add("active");
  btnSale.classList.remove("active");

  rentSection.classList.remove("hidden");
  saleSection.classList.add("hidden");
});
