/*---------- Property Modal Popup ----------*/

const modal = document.getElementById("propertyModal");

const modalImg = document.getElementById("modalImg");
const modalTitle = document.getElementById("modalTitle");
const modalIcons = document.getElementById("modalIcons");

const modalLocation = document.getElementById("modalLocation");
const modalPrice = document.getElementById("modalPrice");
const modalSize = document.getElementById("modalSize");
const modalBedrooms = document.getElementById("modalBedrooms");
const modalExtras = document.getElementById("modalExtras");
const modalDescription = document.getElementById("modalDescription");

const closeModal = document.querySelector(".close-modal");

const modalImgPrev = document.getElementById("modalImgPrev");
const modalImgNext = document.getElementById("modalImgNext");
const modalThumbnails = document.getElementById("modalThumbnails");

let currentGalleryImages = [];
let currentGalleryIndex = 0;

function showGalleryImage(index) {
  if (currentGalleryImages.length === 0) return;

  currentGalleryIndex =
    (index + currentGalleryImages.length) % currentGalleryImages.length;
  modalImg.src = currentGalleryImages[currentGalleryIndex];

  // Highlight the matching thumbnail
  modalThumbnails.querySelectorAll(".modal-thumb").forEach((thumb, i) => {
    thumb.classList.toggle("active", i === currentGalleryIndex);
  });
}

function renderGalleryThumbnails() {
  if (currentGalleryImages.length <= 1) {
    modalThumbnails.innerHTML = "";
    return;
  }

  modalThumbnails.innerHTML = currentGalleryImages
    .map(
      (src, i) => `
        <img
          src="${src}"
          class="modal-thumb${i === currentGalleryIndex ? " active" : ""}"
          data-index="${i}"
          alt="Property photo ${i + 1}"
        />
      `,
    )
    .join("");
}

document.querySelectorAll(".property-card").forEach((card) => {
  const viewBtn = card.querySelector(".view-property-btn");

  if (!viewBtn) return;

  viewBtn.addEventListener("click", () => {
    const imagesAttr = card.dataset.images || "";
    currentGalleryImages = imagesAttr
      .split(",")
      .map((s) => s.trim())
      .filter(Boolean);

    if (currentGalleryImages.length === 0) {
      const img = card.querySelector("img");
      if (img) currentGalleryImages = [img.src];
    }

    currentGalleryIndex = 0;
    showGalleryImage(0);
    renderGalleryThumbnails();

    const hasMultiple = currentGalleryImages.length > 1;
    if (modalImgPrev)
      modalImgPrev.style.display = hasMultiple ? "flex" : "none";
    if (modalImgNext)
      modalImgNext.style.display = hasMultiple ? "flex" : "none";

    const titleEl = card.querySelector("h3");
    modalTitle.textContent = titleEl ? titleEl.textContent : "";

    modalLocation.textContent = card.dataset.location;

    modalPrice.textContent = card.dataset.price;

    const icons = card.querySelector(".icons");
    modalIcons.innerHTML = icons ? icons.innerHTML : "";

    modalSize.textContent = card.dataset.size;

    modalBedrooms.textContent = card.dataset.bedrooms;

    modalExtras.textContent = card.dataset.extras;

    modalDescription.textContent = card.dataset.description;

    modal.classList.remove("hidden");
  });
});

closeModal.addEventListener("click", () => {
  modal.classList.add("hidden");
});

if (modalImgPrev) {
  modalImgPrev.addEventListener("click", () => {
    showGalleryImage(currentGalleryIndex - 1);
  });
}

if (modalImgNext) {
  modalImgNext.addEventListener("click", () => {
    showGalleryImage(currentGalleryIndex + 1);
  });
}

if (modalThumbnails) {
  modalThumbnails.addEventListener("click", (e) => {
    const thumb = e.target.closest(".modal-thumb");
    if (thumb) {
      showGalleryImage(Number(thumb.dataset.index));
    }
  });
}

document.addEventListener("keydown", (e) => {
  if (modal.classList.contains("hidden")) return;
  if (e.key === "ArrowLeft") showGalleryImage(currentGalleryIndex - 1);
  if (e.key === "ArrowRight") showGalleryImage(currentGalleryIndex + 1);
});

modal.addEventListener("click", (e) => {
  if (e.target === modal) {
    modal.classList.add("hidden");
  }
});

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && !modal.classList.contains("hidden")) {
    modal.classList.add("hidden");
  }
});

//--------------------------------------

document.addEventListener("click", (e) => {
  if (e.target.id === "contactAgentBtn") {
    const propertyName = modalTitle.textContent;

    modal.classList.add("hidden");

    document.getElementById("contact").scrollIntoView({
      behavior: "smooth",
    });

    setTimeout(() => {
      const message = document.getElementById("contactMessage");

      if (message) {
        message.value = "I am interested in the property: " + propertyName;
      }
    }, 700);
  }
});

const inquireBtn = document.getElementById("inquireBtn");

if (inquireBtn) {
  inquireBtn.addEventListener("click", () => {
    const propertyName = modalTitle.textContent;

    showToast(
      `Your inquiry about "${propertyName}" has been sent. We will contact you shortly.`,
      "success",
    );

    modal.classList.add("hidden");
  });
}
