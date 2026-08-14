function setButtonLoading(button, isLoading, loadingText = "Please wait...") {
  if (!button) return;

  if (isLoading) {
    button.dataset.originalText = button.innerHTML;

    button.disabled = true;
    button.classList.add("btn-loading");
    button.innerHTML = `<span class="btn-spinner"></span> ${loadingText}`;
  } else {
    button.disabled = false;
    button.classList.remove("btn-loading");

    if (button.dataset.originalText) {
      button.innerHTML = button.dataset.originalText;
      delete button.dataset.originalText;
    }
  }
}
