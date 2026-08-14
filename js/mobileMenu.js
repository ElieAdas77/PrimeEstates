const mobileMenuBtn = document.getElementById("mobileMenuBtn");
const mainNav = document.getElementById("mainNav");

if (mobileMenuBtn && mainNav) {
  mobileMenuBtn.addEventListener("click", function () {
    mainNav.classList.toggle("active");

    const icon = mobileMenuBtn.querySelector("i");

    if (mainNav.classList.contains("active")) {
      icon.classList.remove("fa-bars");
      icon.classList.add("fa-xmark");
    } else {
      icon.classList.remove("fa-xmark");
      icon.classList.add("fa-bars");
    }
  });

  // Close menu after clicking a normal navigation link
  mainNav.querySelectorAll("a").forEach(function (link) {
    link.addEventListener("click", function () {
      if (!link.classList.contains("nav-btn")) {
        mainNav.classList.remove("active");

        const icon = mobileMenuBtn.querySelector("i");
        icon.classList.remove("fa-xmark");
        icon.classList.add("fa-bars");
      }
    });
  });
}
