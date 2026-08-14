function isLoggedInForFavorites() {
  return !!localStorage.getItem("primeEstatesUser");
}

// Mark the heart icons that match the user's already-favorited properties
async function markFavoritedCards() {
  if (!isLoggedInForFavorites()) return;

  try {
    const response = await fetch("php/myFavorites.php");
    const result = await response.json();

    if (!result.success) return;

    const favoritedIds = result.favorites.map((f) => String(f.id));

    document.querySelectorAll(".favorite-btn").forEach((btn) => {
      if (favoritedIds.includes(btn.dataset.id)) {
        btn.classList.add("active");
        btn.querySelector("i").classList.remove("fa-regular");
        btn.querySelector("i").classList.add("fa-solid");
      }
    });
  } catch (error) {
    console.error(error);
  }
}

document.addEventListener("click", async (e) => {
  const btn = e.target.closest(".favorite-btn");
  if (!btn) return;

  e.preventDefault();
  e.stopPropagation();

  if (!isLoggedInForFavorites()) {
    const authModal = document.getElementById("authModal");
    const registerForm = document.getElementById("registerForm");
    const loginForm = document.getElementById("loginForm");
    const authTitle = document.getElementById("authTitle");
    const authSubtitle = document.getElementById("authSubtitle");

    if (authModal) {
      authModal.classList.remove("hidden");
      registerForm.classList.remove("hidden");
      loginForm.classList.add("hidden");
      authTitle.textContent = "Create Account";
      authSubtitle.textContent =
        "Create an account to save your favorite properties.";
    }
    return;
  }

  const propertyId = btn.dataset.id;
  const icon = btn.querySelector("i");

  btn.disabled = true;

  try {
    const formData = new FormData();
    formData.append("csrfToken", window.CSRF_TOKEN);
    formData.append("propertyId", propertyId);

    const response = await fetch("php/toggleFavorite.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      btn.classList.toggle("active", result.favorited);
      icon.classList.toggle("fa-solid", result.favorited);
      icon.classList.toggle("fa-regular", !result.favorited);
    } else {
      showToast(result.message || "Could not update favorite.", "error");
    }
  } catch (error) {
    console.error(error);
    showToast("Could not connect to the server.", "error");
  } finally {
    btn.disabled = false;
  }
});

markFavoritedCards();
