const statusTabs = document.querySelectorAll(".status-tab");
const adminPropertiesList = document.getElementById("adminPropertiesList");

let currentStatus = "pending";

async function loadProperties(status) {
  adminPropertiesList.innerHTML = "<p>Loading properties...</p>";

  try {
    const response = await fetch(`php/adminProperties.php?status=${status}`);
    const result = await response.json();

    if (!result.success) {
      adminPropertiesList.innerHTML = `<p>${result.message}</p>`;
      return;
    }

    if (result.properties.length === 0) {
      adminPropertiesList.innerHTML = "<p>No properties in this category.</p>";
      return;
    }

    adminPropertiesList.innerHTML = result.properties
      .map((p) => renderPropertyCard(p))
      .join("");

    attachActionListeners();
  } catch (error) {
    console.error(error);
    adminPropertiesList.innerHTML = "<p>Could not load properties.</p>";
  }
}

function renderImageStrip(imagesCsv) {
  if (!imagesCsv) {
    return `<div class="admin-image-strip admin-image-strip-empty">No images uploaded</div>`;
  }

  const filenames = imagesCsv
    .split(",")
    .map((f) => f.trim())
    .filter(Boolean);

  if (filenames.length === 0) {
    return `<div class="admin-image-strip admin-image-strip-empty">No images uploaded</div>`;
  }

  const thumbs = filenames
    .map(
      (filename) => `
        <img
          src="uploads/properties/${encodeURIComponent(filename)}"
          class="admin-thumb"
          alt="Property photo"
        />
      `,
    )
    .join("");

  return `<div class="admin-image-strip">${thumbs}</div>`;
}

function renderPropertyCard(p) {
  const submittedDate = new Date(p.created_at).toLocaleDateString();

  const actions =
    p.status === "pending"
      ? `
        <button class="admin-btn approve-btn" data-id="${p.id}" data-status="approved">
          <i class="fa-solid fa-check"></i> Approve
        </button>
        <button class="admin-btn reject-btn" data-id="${p.id}" data-status="rejected">
          <i class="fa-solid fa-xmark"></i> Reject
        </button>
      `
      : `
        <button class="admin-btn reset-btn" data-id="${p.id}" data-status="pending">
          <i class="fa-solid fa-rotate-left"></i> Reset to Pending
        </button>
      `;

  return `
    <div class="admin-property-card">
      ${renderImageStrip(p.images)}

      <div class="admin-property-info">
        <div class="admin-property-title-row">
          <h3>${escapeHtml(p.title)}</h3>
          <span class="property-status-badge ${p.status}">${p.status}</span>
        </div>

        <p class="admin-property-meta">
          ${escapeHtml(p.location)} · $${Number(p.price).toLocaleString()} ·
          ${escapeHtml(p.listing_type)} · ${escapeHtml(p.property_type)} ·
          ${p.area} m² · Ref: ${escapeHtml(p.reference_code || "-")}
        </p>

        <p class="admin-property-desc">${escapeHtml(p.description)}</p>

        <p class="admin-property-owner">
          Submitted by ${escapeHtml(p.owner_name)} (${escapeHtml(p.owner_email)})
          on ${submittedDate}
        </p>
      </div>

      <div class="admin-property-actions">
        ${actions}
      </div>
    </div>
  `;
}

function escapeHtml(str) {
  const div = document.createElement("div");
  div.textContent = str ?? "";
  return div.innerHTML;
}

const imageLightbox = document.getElementById("imageLightbox");
const imageLightboxImg = document.getElementById("imageLightboxImg");
const imageLightboxClose = document.getElementById("imageLightboxClose");

document.addEventListener("click", (e) => {
  const thumb = e.target.closest(".admin-thumb");
  if (thumb && imageLightbox) {
    imageLightboxImg.src = thumb.src;
    imageLightbox.classList.remove("hidden");
  }
});

if (imageLightboxClose) {
  imageLightboxClose.addEventListener("click", () => {
    imageLightbox.classList.add("hidden");
  });
}

if (imageLightbox) {
  imageLightbox.addEventListener("click", (e) => {
    if (e.target === imageLightbox) {
      imageLightbox.classList.add("hidden");
    }
  });
}

document.addEventListener("keydown", (e) => {
  if (
    e.key === "Escape" &&
    imageLightbox &&
    !imageLightbox.classList.contains("hidden")
  ) {
    imageLightbox.classList.add("hidden");
  }
});

function attachActionListeners() {
  document.querySelectorAll(".admin-btn").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const propertyId = btn.dataset.id;
      const newStatus = btn.dataset.status;

      btn.disabled = true;

      try {
        const formData = new FormData();
        formData.append("csrfToken", window.CSRF_TOKEN);
        formData.append("propertyId", propertyId);
        formData.append("status", newStatus);

        const response = await fetch("php/updatePropertyStatus.php", {
          method: "POST",
          body: formData,
        });

        const result = await response.json();

        if (result.success) {
          loadProperties(currentStatus);
        } else {
          alert(result.message || "Could not update property.");
          btn.disabled = false;
        }
      } catch (error) {
        console.error(error);
        alert("Could not connect to the server.");
        btn.disabled = false;
      }
    });
  });
}

statusTabs.forEach((tab) => {
  tab.addEventListener("click", () => {
    statusTabs.forEach((t) => t.classList.remove("active"));
    tab.classList.add("active");

    currentStatus = tab.dataset.status;
    loadProperties(currentStatus);
  });
});

loadProperties(currentStatus);

const sectionTabs = document.querySelectorAll(".section-tab");
const propertiesSection = document.getElementById("propertiesSection");
const messagesSection = document.getElementById("messagesSection");
const adminMessagesList = document.getElementById("adminMessagesList");

sectionTabs.forEach((tab) => {
  tab.addEventListener("click", () => {
    sectionTabs.forEach((t) => t.classList.remove("active"));
    tab.classList.add("active");

    if (tab.dataset.section === "messages") {
      propertiesSection.classList.add("hidden");
      messagesSection.classList.remove("hidden");

      loadMessages();
    } else {
      messagesSection.classList.add("hidden");
      propertiesSection.classList.remove("hidden");
    }
  });
});

async function loadMessages() {
  adminMessagesList.innerHTML = "<p>Loading messages...</p>";

  try {
    const response = await fetch("php/adminMessages.php");
    const result = await response.json();

    if (!result.success) {
      adminMessagesList.innerHTML = `<p>${result.message}</p>`;
      return;
    }

    if (result.messages.length === 0) {
      adminMessagesList.innerHTML = "<p>No messages yet.</p>";
      return;
    }

    adminMessagesList.innerHTML = result.messages
      .map((m) => renderMessageCard(m))
      .join("");
  } catch (error) {
    console.error(error);
    adminMessagesList.innerHTML = "<p>Could not load messages.</p>";
  }
}

function renderMessageCard(m) {
  const sentDate = new Date(m.created_at).toLocaleString();

  return `
    <div class="admin-property-card">
      <div class="admin-property-info">
        <div class="admin-property-title-row">
          <h3>${escapeHtml(m.subject)}</h3>
        </div>

        <p class="admin-property-meta">
          From ${escapeHtml(m.name)} (${escapeHtml(m.email)}) · ${sentDate}
        </p>

        <p class="admin-property-desc">${escapeHtml(m.message)}</p>
      </div>
    </div>
  `;
}
