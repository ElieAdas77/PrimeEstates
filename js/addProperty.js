// ADD PROPERTY POPUP

const addPropertyBtn = document.getElementById("addPropertyBtn");
const addPropertyModal = document.getElementById("addPropertyModal");

const propertyFormModal = document.getElementById("propertyFormModal");
const propertyFormClose = document.getElementById("propertyFormClose");

const addPropertyClose = document.getElementById("addPropertyClose");

const signInBtn = document.getElementById("signInBtn");
const signUpBtn = document.getElementById("signUpBtn");

// OPEN ADD PROPERTY POPUP
addPropertyBtn.addEventListener("click", function (e) {
  e.preventDefault();

  // Check if user is already logged in
  if (localStorage.getItem("primeEstatesUser")) {
    // User is logged in → open property form directly
    propertyFormModal.classList.remove("hidden");
  } else {
    // User is not logged in → show sign in / sign up popup
    addPropertyModal.classList.remove("hidden");
  }
});

// CLOSE ADD prop
addPropertyClose.addEventListener("click", () => {
  addPropertyModal.classList.add("hidden");
});

// CLICK OUTSIDE
addPropertyModal.addEventListener("click", (e) => {
  if (e.target === addPropertyModal) {
    addPropertyModal.classList.add("hidden");
  }
});

// SIGN IN
signInBtn.addEventListener("click", () => {
  addPropertyModal.classList.add("hidden");

  authModal.classList.remove("hidden");

  registerForm.classList.add("hidden");
  loginForm.classList.remove("hidden");

  authTitle.textContent = "Welcome Back";
  authSubtitle.textContent = "Sign in to add your property to PrimeEstates.";
});

// CREATE ACCOUNT
signUpBtn.addEventListener("click", () => {
  addPropertyModal.classList.add("hidden");

  authModal.classList.remove("hidden");

  loginForm.classList.add("hidden");
  registerForm.classList.remove("hidden");

  authTitle.textContent = "Create Account";
  authSubtitle.textContent =
    "Create an account to add your property to PrimeEstates.";
});

// ESCAPE
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && !addPropertyModal.classList.contains("hidden")) {
    addPropertyModal.classList.add("hidden");
  }
});

// OPEN PROPERTY FORM AFTER LOGIN / REGISTER

//const propertyFormModal = document.getElementById("propertyFormModal");

//const propertyFormClose = document.getElementById("propertyFormClose");

propertyFormClose.addEventListener("click", () => {
  propertyFormModal.classList.add("hidden");
});

propertyFormModal.addEventListener("click", (e) => {
  if (e.target === propertyFormModal) {
    propertyFormModal.classList.add("hidden");
  }
});

// FUNCTION TO OPEN PROPERTY FORM
function openPropertyForm() {
  authModal.classList.add("hidden");
  addPropertyModal.classList.add("hidden");

  propertyFormModal.classList.remove("hidden");
}

//sucesss formmmmmmmm
const propertyForm = document.getElementById("propertyForm");
const propertySuccessMessage = document.getElementById(
  "propertySuccessMessage",
);
const propertySuccessText = document.getElementById("propertySuccessText");
const successCloseBtn = document.getElementById("successCloseBtn");
const notifyAgentBtn = document.getElementById("notifyAgentBtn");

// Keep track of the property we just submitted so the
// "Message an Agent Now" button knows what to reference
let lastSubmittedProperty = null;

function getCheckedValue(name) {
  const el = document.querySelector(`input[name="${name}"]:checked`);
  return el ? el.value : "";
}

function getCheckedValues(name) {
  return Array.from(
    document.querySelectorAll(`input[name="${name}"]:checked`),
  ).map((el) => el.value);
}

if (propertyForm && propertySuccessMessage) {
  propertyForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const submitBtn = propertyForm.querySelector(".property-submit-btn");
    setButtonLoading(submitBtn, true, "Submitting...");

    const formData = new FormData();
    formData.append("csrfToken", window.CSRF_TOKEN);

    formData.append(
      "location",
      document.getElementById("propertyLocation").value.trim(),
    );
    formData.append(
      "propertyType",
      document.getElementById("propertyTypeForm").value,
    );
    formData.append("price", document.getElementById("propertyPrice").value);
    formData.append(
      "title",
      document.getElementById("propertyTitle").value.trim(),
    );
    formData.append("area", document.getElementById("propertyArea").value);

    formData.append("listingType", getCheckedValue("listingType"));
    formData.append("furnished", getCheckedValue("furnished"));
    formData.append("condition", getCheckedValue("condition"));
    formData.append("ownership", getCheckedValue("ownership"));

    getCheckedValues("paymentType").forEach((v) =>
      formData.append("paymentType[]", v),
    );
    getCheckedValues("amenities").forEach((v) =>
      formData.append("amenities[]", v),
    );

    formData.append("bedrooms", document.getElementById("bedrooms").value);
    formData.append("bathrooms", document.getElementById("bathrooms").value);
    formData.append("floor", document.getElementById("floor").value);
    formData.append("parking", document.getElementById("parking").value);
    formData.append("terrace", document.getElementById("terrace").value);
    formData.append("garden", document.getElementById("garden").value);
    formData.append("monthlyFee", document.getElementById("monthlyFee").value);
    formData.append("yearBuilt", document.getElementById("yearBuilt").value);

    formData.append(
      "reference",
      document.getElementById("propertyReference").value.trim(),
    );
    formData.append(
      "description",
      document.getElementById("propertyDescription").value.trim(),
    );
    formData.append("videoLink", document.getElementById("videoLink").value);

    const imageFiles = document.getElementById("propertyImages").files;
    const MAX_IMAGE_BYTES = 10 * 1024 * 1024; // keep in sync with imageUploadHelper.php
    const oversized = [];

    for (let i = 0; i < imageFiles.length; i++) {
      if (imageFiles[i].size > MAX_IMAGE_BYTES) {
        oversized.push(imageFiles[i].name);
        continue; // don't bother sending it, it'll just get rejected server-side
      }
      formData.append("images[]", imageFiles[i]);
    }

    if (oversized.length > 0) {
      showToast(
        `Skipping ${oversized.length} image(s) over 10MB: ${oversized.join(", ")}`,
        "error",
      );
    }

    try {
      const response = await fetch("php/addProperty.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        lastSubmittedProperty = {
          title: result.title,
          reference: result.reference,
        };

        if (propertySuccessText) {
          let text = `"${result.title}" (Ref: ${result.reference}) has been received, but it won't go live until an agent reviews it. Send us a quick message so an agent can follow up and approve your listing.`;

          if (result.skippedImages && result.skippedImages.length > 0) {
            text += ` Note: ${result.skippedImages.length} image(s) couldn't be saved - ${result.skippedImages.join("; ")}.`;
          }

          propertySuccessText.textContent = text;
        }

        propertyForm.classList.add("hidden");
        propertySuccessMessage.classList.remove("hidden");
      } else {
        showToast(
          result.message || "Something went wrong. Please try again.",
          "error",
        );
      }
    } catch (error) {
      console.error(error);
      showToast("Could not connect to the server.", "error");
    } finally {
      setButtonLoading(submitBtn, false);
    }
  });
}

// MESSAGE AN AGENT NOW  jump to contact form, pre-filled

if (notifyAgentBtn) {
  notifyAgentBtn.addEventListener("click", () => {
    propertyFormModal.classList.add("hidden");
    propertySuccessMessage.classList.add("hidden");
    propertyForm.classList.remove("hidden");
    propertyForm.reset();

    document.getElementById("contact").scrollIntoView({ behavior: "smooth" });

    setTimeout(() => {
      const subject = document.getElementById("contactSubject");
      const message = document.getElementById("contactMessage");

      if (subject && lastSubmittedProperty) {
        subject.value = "New Property Listing Review";
      }

      if (message && lastSubmittedProperty) {
        message.value = `Hi, I just submitted a new property "${lastSubmittedProperty.title}" (Ref: ${lastSubmittedProperty.reference}) and would like an agent to review and approve it.`;
      }
    }, 700);
  });
}

if (successCloseBtn) {
  successCloseBtn.addEventListener("click", function () {
    propertySuccessMessage.classList.add("hidden");
    propertyForm.classList.remove("hidden");

    // Reset the form
    propertyForm.reset();
  });
}
