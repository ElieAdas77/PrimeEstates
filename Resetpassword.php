<?php


require_once "php/config.php";

$token = $_GET["token"] ?? "";
$tokenValid = false;
$userId = null;

if ($token !== "") {
    $stmt = $conn->prepare(
        "SELECT user_id FROM password_resets
         WHERE token = ? AND used = 0 AND expires_at > NOW()"
    );
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tokenValid = true;
        $userId = $result->fetch_assoc()["user_id"];
    }
    $stmt->close();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset Password - PrimeEstates</title>
  <link rel="stylesheet" href="main22.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh; background:#f8f8f8;">

  <div class="auth-box" style="position:static;">
    <div class="auth-header">
      <h2>Reset Your Password</h2>
    </div>

    <?php if (!$tokenValid): ?>
      <p style="text-align:center; color:#b30000; margin-bottom:20px;">
        This reset link is invalid or has expired. Please request a new one.
      </p>
      <a href="index.php" class="auth-submit" style="display:block; text-align:center; text-decoration:none;">
        Back to PrimeEstates
      </a>
    <?php else: ?>
      <form id="resetPasswordForm">
        <input type="hidden" id="resetToken" value="<?php echo htmlspecialchars($token); ?>" />
        <input type="hidden" id="csrfTokenField" value="<?php echo htmlspecialchars(getCsrfToken()); ?>" />

        <div class="auth-field">
          <label>New Password</label>
          <input type="password" id="newPassword" placeholder="At least 8 characters" required />
        </div>

        <div class="auth-field">
          <label>Confirm New Password</label>
          <input type="password" id="confirmPassword" placeholder="Re-enter your new password" required />
        </div>

        <button type="submit" class="auth-submit">Reset Password</button>
      </form>
      <p id="resetStatusMessage" style="text-align:center; margin-top:15px;"></p>
    <?php endif; ?>
  </div>

  <script>
    const resetForm = document.getElementById("resetPasswordForm");

    if (resetForm) {
      resetForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const token = document.getElementById("resetToken").value;
        const newPassword = document.getElementById("newPassword").value;
        const confirmPassword = document.getElementById("confirmPassword").value;
        const statusEl = document.getElementById("resetStatusMessage");
        const submitBtn = resetForm.querySelector(".auth-submit");

        if (newPassword.length < 8) {
          statusEl.textContent = "Password must be at least 8 characters.";
          statusEl.style.color = "#b30000";
          return;
        }

        if (newPassword !== confirmPassword) {
          statusEl.textContent = "Passwords do not match.";
          statusEl.style.color = "#b30000";
          return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = "Resetting...";

        try {
          const formData = new FormData();
          formData.append("token", token);
          formData.append("csrfToken", document.getElementById("csrfTokenField").value);
          formData.append("newPassword", newPassword);

          const response = await fetch("php/resetPasswordSubmit.php", {
            method: "POST",
            body: formData,
          });

          const result = await response.json();

          statusEl.textContent = result.message;
          statusEl.style.color = result.success ? "#28a745" : "#b30000";

          if (result.success) {
            resetForm.reset();
            resetForm.style.display = "none";
            setTimeout(() => {
              window.location.href = "index.php";
            }, 2500);
          } else {
            submitBtn.disabled = false;
            submitBtn.textContent = "Reset Password";
          }
        } catch (error) {
          console.error(error);
          statusEl.textContent = "Could not connect to the server.";
          statusEl.style.color = "#b30000";
          submitBtn.disabled = false;
          submitBtn.textContent = "Reset Password";
        }
      });
    }
  </script>
</body>
</html>