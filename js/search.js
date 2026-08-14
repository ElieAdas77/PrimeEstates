const searchBtn = document.getElementById("searchBtn");
const searchInput = document.getElementById("searchInput");
const propertyType = document.getElementById("propertyType");
const priceFilter = document.getElementById("priceFilter");
const bedroomFilter = document.getElementById("bedroomFilter");
const clearFilters = document.getElementById("clearFilters");
const resultsCount = document.getElementById("resultsCount");

if (
  searchBtn &&
  searchInput &&
  propertyType &&
  priceFilter &&
  bedroomFilter &&
  clearFilters
) {
  const allProperties = document.querySelectorAll(".property-card");

  function filterProperties() {
    const locationValue = searchInput.value.toLowerCase().trim();

    const typeValue = propertyType.value;

    const maxPrice = priceFilter.value;

    const minBedrooms = bedroomFilter.value;

    let visibleCount = 0;

    allProperties.forEach((card) => {
      const location = (card.dataset.location || "").toLowerCase();

      const type = card.dataset.type || "all";

      const priceText = card.dataset.price || "0";

      const bedrooms = Number(card.dataset.bedrooms || 0);

      const price = Number(priceText.replace(/[^0-9]/g, ""));

      // Location
      const locationMatch =
        locationValue === "" || location.includes(locationValue);

      // Property type
      const typeMatch = typeValue === "all" || type === typeValue;

      // Maximum price
      const priceMatch = maxPrice === "all" || price <= Number(maxPrice);

      // Minimum bedrooms
      const bedroomMatch =
        minBedrooms === "all" || bedrooms >= Number(minBedrooms);

      // Show property
      if (locationMatch && typeMatch && priceMatch && bedroomMatch) {
        card.style.display = "";

        visibleCount++;
      } else {
        card.style.display = "none";
      }
    });

    if (resultsCount) {
      if (visibleCount === 0) {
        resultsCount.textContent = "No properties match your search.";
      } else if (visibleCount === 1) {
        resultsCount.textContent = "Showing 1 property";
      } else {
        resultsCount.textContent = `Showing ${visibleCount} properties`;
      }
    }

    if (typeof refreshPropertyPagination === "function") {
      refreshPropertyPagination("saleProperties");
      refreshPropertyPagination("rentProperties");
    }
  }

  searchBtn.addEventListener("click", () => {
    filterProperties();
  });

  searchInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      filterProperties();
    }
  });

  clearFilters.addEventListener("click", () => {
    searchInput.value = "";

    propertyType.value = "all";

    priceFilter.value = "all";

    bedroomFilter.value = "all";

    allProperties.forEach((card) => {
      card.style.display = "";
    });

    if (resultsCount) {
      resultsCount.textContent = "Showing all properties";
    }

    if (typeof refreshPropertyPagination === "function") {
      refreshPropertyPagination("saleProperties");
      refreshPropertyPagination("rentProperties");
    }
  });
}
