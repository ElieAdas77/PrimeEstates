// --- Slideshow Script ---***************************************

document.addEventListener("DOMContentLoaded", () => {
  const slides = document.querySelectorAll(".slide-image");
  let currentSlide = 0;
  const slideInterval = 5000; // Change image every 5 seconds

  if (slides.length > 0) {
    function nextSlide() {
      slides[currentSlide].classList.remove("active-slide");

      currentSlide = (currentSlide + 1) % slides.length;

      slides[currentSlide].classList.add("active-slide");
    }

    slides[0].classList.add("active-slide");

    setInterval(nextSlide, slideInterval);
  }
});
