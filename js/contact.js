// Contact form
const contactForm = document.getElementById("contactForm");

if (contactForm) {
  contactForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const name = document.getElementById("contactName").value.trim();
    const email = document.getElementById("contactEmail").value.trim();
    const subject = document.getElementById("contactSubject").value.trim();
    const message = document.getElementById("contactMessage").value.trim();

    const submitBtn = contactForm.querySelector(".contact-submit");
    setButtonLoading(submitBtn, true, "Sending...");

    const formData = new FormData();
    formData.append("csrfToken", window.CSRF_TOKEN);
    formData.append("name", name);
    formData.append("email", email);
    formData.append("subject", subject);
    formData.append("message", message);

    try {
      const response = await fetch("php/contact.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        showToast(`Thank you, ${name}! ${result.message}`, "success");
        contactForm.reset();
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
