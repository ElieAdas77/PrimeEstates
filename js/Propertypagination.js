const PROPERTY_PAGE_SIZE = 4;

const propertyPaginationState = {};

function setupPropertyPagination(listId) {
  const list = document.getElementById(listId);
  if (!list) return;

  const controls = document.createElement("div");
  controls.className = "pagination-controls";
  controls.innerHTML = `
    <button type="button" class="page-prev" aria-label="Previous properties">
      <i class="fa-solid fa-chevron-left"></i>
    </button>
    <span class="page-indicator"></span>
    <button type="button" class="page-next" aria-label="Next properties">
      <i class="fa-solid fa-chevron-right"></i>
    </button>
  `;
  list.appendChild(controls);

  controls
    .querySelector(".page-prev")
    .addEventListener("click", () => changePropertyPage(listId, -1));

  controls
    .querySelector(".page-next")
    .addEventListener("click", () => changePropertyPage(listId, 1));

  propertyPaginationState[listId] = 1;
  renderPropertyPage(listId);
}

function getPaginationCandidates(list) {
  return Array.from(list.querySelectorAll(".property-card")).filter(
    (card) => card.style.display !== "none",
  );
}

function renderPropertyPage(listId) {
  const list = document.getElementById(listId);
  if (!list) return;

  const candidates = getPaginationCandidates(list);
  const totalPages = Math.max(
    1,
    Math.ceil(candidates.length / PROPERTY_PAGE_SIZE),
  );

  if (!propertyPaginationState[listId]) {
    propertyPaginationState[listId] = 1;
  }
  if (propertyPaginationState[listId] > totalPages) {
    propertyPaginationState[listId] = totalPages;
  }

  const currentPage = propertyPaginationState[listId];
  const start = (currentPage - 1) * PROPERTY_PAGE_SIZE;
  const end = start + PROPERTY_PAGE_SIZE;

  candidates.forEach((card, index) => {
    card.classList.toggle("page-hidden", index < start || index >= end);
  });

  const controls = list.querySelector(".pagination-controls");
  if (controls) {
    controls.querySelector(".page-indicator").textContent =
      `Page ${currentPage} of ${totalPages}`;

    controls.querySelector(".page-prev").disabled = currentPage <= 1;
    controls.querySelector(".page-next").disabled = currentPage >= totalPages;

    controls.style.display = totalPages <= 1 ? "none" : "flex";
  }
}

function changePropertyPage(listId, direction) {
  propertyPaginationState[listId] =
    (propertyPaginationState[listId] || 1) + direction;
  renderPropertyPage(listId);
}

function refreshPropertyPagination(listId) {
  propertyPaginationState[listId] = 1;
  renderPropertyPage(listId);
}

setupPropertyPagination("saleProperties");
setupPropertyPagination("rentProperties");
