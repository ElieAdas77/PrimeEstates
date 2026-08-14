// scroll animation for property cards
const cards = document.querySelectorAll(".fade-in");

const observer = new IntersectionObserver( //watches elements as you scoll
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("show");
      }
    });
  },
  { threshold: 0.3 }, //trigger el animation  30%
);

cards.forEach((card) => observer.observe(card));
