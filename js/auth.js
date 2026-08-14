// REGISTER / LOGIN MODAL

const registerBtn = document.getElementById("registerBtn");

const authModal = document.getElementById("authModal");

const authClose = document.getElementById("authClose");

const registerForm = document.getElementById("registerForm");

const loginForm = document.getElementById("loginForm");

const showLogin = document.getElementById("showLogin");

const showRegister = document.getElementById("showRegister");

const authTitle = document.getElementById("authTitle");

const authSubtitle = document.getElementById("authSubtitle");

//  (My Properties / Favorites / Logout)

const userMenu = document.getElementById("userMenu");
const userDropdown = document.getElementById("userDropdown");
const myPropertiesBtn = document.getElementById("myPropertiesBtn");
const favoritesBtn = document.getElementById("favoritesBtn");
const logoutBtn = document.getElementById("logoutBtn");

function isLoggedIn() {
  return !!localStorage.getItem("primeEstatesUser");
}

// OPEN REGISTER MODAL, OR TOGGLE DROPDOWN IF ALREADY LOGGED IN

registerBtn.addEventListener("click", (e) => {
  e.preventDefault();

  if (isLoggedIn()) {
    userDropdown.classList.toggle("hidden");
    return;
  }

  authModal.classList.remove("hidden");

  registerForm.classList.remove("hidden");

  loginForm.classList.add("hidden");

  authTitle.textContent = "Create Account";

  authSubtitle.textContent =
    "Create an account to save your favorite properties.";
});

document.addEventListener("click", (e) => {
  if (!userMenu.contains(e.target)) {
    userDropdown.classList.add("hidden");
  }
});

// LOGOUT

logoutBtn.addEventListener("click", async (e) => {
  e.preventDefault();

  try {
    const logoutData = new FormData();
    logoutData.append("csrfToken", window.CSRF_TOKEN);
    await fetch("php/logout.php", { method: "POST", body: logoutData });
  } catch (error) {
    console.error(error);
  }

  localStorage.removeItem("primeEstatesUser");
  localStorage.removeItem("primeEstatesEmail");
  localStorage.removeItem("primeEstatesRole");

  registerBtn.textContent = "Register";

  userDropdown.classList.add("hidden");
});

// MY PROP open modal and load real data

const myPropertiesModal = document.getElementById("myPropertiesModal");
const myPropertiesClose = document.getElementById("myPropertiesClose");
const myPropertiesList = document.getElementById("myPropertiesList");

async function loadMyProperties() {
  myPropertiesList.innerHTML = "<p>Loading your properties...</p>";

  try {
    const response = await fetch("php/myProperties.php");
    const result = await response.json();

    if (!result.success) {
      myPropertiesList.innerHTML = `<p>${escapeHtml(result.message)}</p>`;
      return;
    }

    if (result.properties.length === 0) {
      myPropertiesList.innerHTML =
        "<p>You haven't submitted any properties yet.</p>";
      return;
    }

    myPropertiesList.innerHTML = result.properties
      .map(
        (p) => `
        <div class="my-property-item">
          <div>
            <h4>${escapeHtml(p.title)}</h4>
            <p>${escapeHtml(p.location)} · $${Number(p.price).toLocaleString()} · ${escapeHtml(p.listing_type)} · Ref: ${escapeHtml(p.reference_code)}</p>
          </div>
          <div style="display:flex; align-items:center; gap:10px;">
            <span class="property-status-badge ${escapeHtml(p.status)}">${escapeHtml(p.status)}</span>
            <button type="button" class="my-property-edit-btn" data-id="${p.id}" aria-label="Edit property">
              <i class="fa-solid fa-pen"></i>
            </button>
            <button type="button" class="my-property-delete-btn" data-id="${p.id}" aria-label="Delete property">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </div>
      `,
      )
      .join("");
  } catch (error) {
    console.error(error);
    myPropertiesList.innerHTML = "<p>Could not load your properties.</p>";
  }
}

myPropertiesBtn.addEventListener("click", async (e) => {
  e.preventDefault();
  userDropdown.classList.add("hidden");

  myPropertiesModal.classList.remove("hidden");
  await loadMyProperties();
});

if (myPropertiesClose) {
  myPropertiesClose.addEventListener("click", () => {
    myPropertiesModal.classList.add("hidden");
  });
}

if (myPropertiesModal) {
  myPropertiesModal.addEventListener("click", (e) => {
    if (e.target === myPropertiesModal) {
      myPropertiesModal.classList.add("hidden");
    }
  });
}

const editPropertyModal = document.getElementById("editPropertyModal");
const editPropertyClose = document.getElementById("editPropertyClose");
const editPropertyForm = document.getElementById("editPropertyForm");

// Event delegation: handles Edit and Delete clicks for any property
// item currently rendered in the My Properties list
myPropertiesList.addEventListener("click", async (e) => {
  const editBtn = e.target.closest(".my-property-edit-btn");
  const deleteBtn = e.target.closest(".my-property-delete-btn");

  if (editBtn) {
    const propertyId = editBtn.dataset.id;

    try {
      const response = await fetch(
        `php/getPropertyForEdit.php?id=${propertyId}`,
      );
      const result = await response.json();

      if (!result.success) {
        showToast(result.message, "error");
        return;
      }

      document.getElementById("editPropertyId").value = result.property.id;
      document.getElementById("editPropertyTitle").value =
        result.property.title;
      document.getElementById("editPropertyPrice").value =
        result.property.price;
      document.getElementById("editPropertyDescription").value =
        result.property.description;
      document.getElementById("editPropertyImages").value = "";

      myPropertiesModal.classList.add("hidden");
      editPropertyModal.classList.remove("hidden");
    } catch (error) {
      console.error(error);
      showToast("Could not load property details.", "error");
    }
  }

  if (deleteBtn) {
    const propertyId = deleteBtn.dataset.id;

    const confirmed = confirm("Delete this property? This cannot be undone.");
    if (!confirmed) return;

    deleteBtn.disabled = true;

    try {
      const formData = new FormData();
      formData.append("csrfToken", window.CSRF_TOKEN);
      formData.append("propertyId", propertyId);

      const response = await fetch("php/deleteProperty.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      showToast(result.message, result.success ? "success" : "error");

      if (result.success) {
        loadMyProperties();
      } else {
        deleteBtn.disabled = false;
      }
    } catch (error) {
      console.error(error);
      showToast("Could not connect to the server.", "error");
      deleteBtn.disabled = false;
    }
  }
});

if (editPropertyClose) {
  editPropertyClose.addEventListener("click", () => {
    editPropertyModal.classList.add("hidden");
  });
}

if (editPropertyModal) {
  editPropertyModal.addEventListener("click", (e) => {
    if (e.target === editPropertyModal) {
      editPropertyModal.classList.add("hidden");
    }
  });
}

if (editPropertyForm) {
  editPropertyForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const submitBtn = editPropertyForm.querySelector(".auth-submit");
    setButtonLoading(submitBtn, true, "Saving...");

    const formData = new FormData();
    formData.append("csrfToken", window.CSRF_TOKEN);
    formData.append(
      "propertyId",
      document.getElementById("editPropertyId").value,
    );
    formData.append(
      "title",
      document.getElementById("editPropertyTitle").value.trim(),
    );
    formData.append(
      "price",
      document.getElementById("editPropertyPrice").value,
    );
    formData.append(
      "description",
      document.getElementById("editPropertyDescription").value.trim(),
    );

    const imageFiles = document.getElementById("editPropertyImages").files;
    for (let i = 0; i < imageFiles.length; i++) {
      formData.append("images[]", imageFiles[i]);
    }

    try {
      const response = await fetch("php/editProperty.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      showToast(result.message, result.success ? "success" : "error");

      if (result.success) {
        editPropertyModal.classList.add("hidden");
        myPropertiesModal.classList.remove("hidden");
        loadMyProperties();
      }
    } catch (error) {
      console.error(error);
      showToast("Could not connect to the server.", "error");
    } finally {
      setButtonLoading(submitBtn, false);
    }
  });
}

const favoritesModal = document.getElementById("favoritesModal");
const favoritesClose = document.getElementById("favoritesClose");
const favoritesList = document.getElementById("favoritesList");

favoritesBtn.addEventListener("click", async (e) => {
  e.preventDefault();
  userDropdown.classList.add("hidden");

  favoritesModal.classList.remove("hidden");
  favoritesList.innerHTML = "<p>Loading your favorites...</p>";

  try {
    const response = await fetch("php/myFavorites.php");
    const result = await response.json();

    if (!result.success) {
      favoritesList.innerHTML = `<p>${result.message}</p>`;
      return;
    }

    if (result.favorites.length === 0) {
      favoritesList.innerHTML =
        "<p>You haven't favorited any properties yet.</p>";
      return;
    }

    favoritesList.innerHTML = result.favorites
      .map(
        (p) => `
        <div class="my-property-item">
          <div>
            <h4>${escapeHtml(p.title)}</h4>
            <p>${escapeHtml(p.location)} · $${Number(p.price).toLocaleString()} · ${escapeHtml(p.listing_type)} · Ref: ${escapeHtml(p.reference_code)}</p>
          </div>
          <span class="property-status-badge ${escapeHtml(p.status)}">${escapeHtml(p.status)}</span>
        </div>
      `,
      )
      .join("");
  } catch (error) {
    console.error(error);
    favoritesList.innerHTML = "<p>Could not load your favorites.</p>";
  }
});

if (favoritesClose) {
  favoritesClose.addEventListener("click", () => {
    favoritesModal.classList.add("hidden");
  });
}

if (favoritesModal) {
  favoritesModal.addEventListener("click", (e) => {
    if (e.target === favoritesModal) {
      favoritesModal.classList.add("hidden");
    }
  });
}

// FORGOT PASS

const forgotPasswordModal = document.getElementById("forgotPasswordModal");
const forgotPasswordClose = document.getElementById("forgotPasswordClose");
const forgotPasswordForm = document.getElementById("forgotPasswordForm");
const showForgotPassword = document.getElementById("showForgotPassword");

if (showForgotPassword) {
  showForgotPassword.addEventListener("click", () => {
    authModal.classList.add("hidden");
    forgotPasswordModal.classList.remove("hidden");
  });
}

if (forgotPasswordClose) {
  forgotPasswordClose.addEventListener("click", () => {
    forgotPasswordModal.classList.add("hidden");
  });
}

if (forgotPasswordModal) {
  forgotPasswordModal.addEventListener("click", (e) => {
    if (e.target === forgotPasswordModal) {
      forgotPasswordModal.classList.add("hidden");
    }
  });
}

if (forgotPasswordForm) {
  forgotPasswordForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const email = document.getElementById("forgotPasswordEmail").value.trim();
    const submitBtn = forgotPasswordForm.querySelector(".auth-submit");

    setButtonLoading(submitBtn, true, "Sending...");

    try {
      const formData = new FormData();
      formData.append("csrfToken", window.CSRF_TOKEN);
      formData.append("email", email);

      const response = await fetch("php/requestPasswordReset.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      showToast(result.message, result.success ? "success" : "error");

      if (result.success) {
        forgotPasswordForm.reset();
        forgotPasswordModal.classList.add("hidden");
      }
    } catch (error) {
      console.error(error);
      showToast("Could not connect to the server.", "error");
    } finally {
      setButtonLoading(submitBtn, false);
    }
  });
}

authClose.addEventListener("click", () => {
  authModal.classList.add("hidden");
});

authModal.addEventListener("click", (e) => {
  if (e.target === authModal) {
    authModal.classList.add("hidden");
  }
});

showLogin.addEventListener("click", () => {
  registerForm.classList.add("hidden");

  loginForm.classList.remove("hidden");

  authTitle.textContent = "Welcome Back";

  authSubtitle.textContent = "Sign in to access your PrimeEstates account.";
});

showRegister.addEventListener("click", () => {
  loginForm.classList.add("hidden");

  registerForm.classList.remove("hidden");

  authTitle.textContent = "Create Account";

  authSubtitle.textContent =
    "Create an account to save your favorite properties.";
});

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && !authModal.classList.contains("hidden")) {
    authModal.classList.add("hidden");
  }
});

registerForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const fullname = document.getElementById("registerName").value.trim();
  const email = document.getElementById("registerEmail").value.trim();
  const password = document.getElementById("registerPassword").value;

  const formData = new FormData();
  formData.append("csrfToken", window.CSRF_TOKEN);

  formData.append("fullname", fullname);
  formData.append("email", email);
  formData.append("password", password);

  try {
    const response = await fetch("php/register.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      // Save the user's name
      localStorage.setItem("primeEstatesUser", result.fullname);

      // Show name in navbar
      registerBtn.textContent = result.fullname;

      showToast("Account created successfully!", "success");

      // Reset form
      registerForm.reset();

      authModal.classList.add("hidden");
    } else {
      showToast(result.message, "error");
    }
  } catch (error) {
    console.error(error);
    showToast("Could not connect to the server.", "error");
  }
});

// VERIFY LOGIN STATE WITH THE SERVER ON LOAD

const savedUser = localStorage.getItem("primeEstatesUser");

if (savedUser) {
  registerBtn.textContent = savedUser;
}

(async function checkSession() {
  try {
    const response = await fetch("php/checkSession.php");
    const result = await response.json();

    if (result.loggedIn) {
      localStorage.setItem("primeEstatesUser", result.fullname);
      localStorage.setItem("primeEstatesEmail", result.email);
      localStorage.setItem("primeEstatesRole", result.role);

      registerBtn.textContent = result.fullname;
    } else {
      // No real session on the server
      localStorage.removeItem("primeEstatesUser");
      localStorage.removeItem("primeEstatesEmail");
      localStorage.removeItem("primeEstatesRole");

      registerBtn.textContent = "Register";
    }
  } catch (error) {
    console.error(error);
  }
})();

// LOGIN WITH DATABASE

loginForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const email = document.getElementById("loginEmail").value.trim();
  const password = document.getElementById("loginPassword").value;

  if (email === "" || password === "") {
    showToast("Please enter your email and password.", "error");
    return;
  }

  const formData = new FormData();
  formData.append("csrfToken", window.CSRF_TOKEN);

  formData.append("email", email);
  formData.append("password", password);

  try {
    const response = await fetch("php/login.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      // Save logged-in user info
      localStorage.setItem("primeEstatesUser", result.fullname);
      localStorage.setItem("primeEstatesEmail", result.email);
      localStorage.setItem("primeEstatesRole", result.role);

      registerBtn.textContent = result.fullname;

      showToast(`Welcome back, ${result.fullname}!`, "success");

      loginForm.reset();

      authModal.classList.add("hidden");
    } else {
      showToast(result.message, "error");
    }
  } catch (error) {
    console.error(error);

    showToast("Could not connect to the server.", "error");
  }
});
