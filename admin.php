<?php

require_once "php/config.php";

// Server-side gate: only real admins get past this line.
// (This checks the PHP session set by login.php - not localStorage,
// so it can't be faked from the browser.)
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: index.php");
    exit();
}

$adminName = $_SESSION["fullname"];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PrimeEstates - Admin Dashboard</title>
  <link rel="stylesheet" href="css/admin.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
  />
</head>
<body>

  <header class="admin-header">
    <h1>PrimeEstates <span>Admin</span></h1>
    <div class="admin-header-right">
      <span>Signed in as <?php echo htmlspecialchars($adminName); ?></span>
      <a href="index.php">Back to site</a>
    </div>
  </header>

  <nav class="admin-section-nav">
    <button class="section-tab active" data-section="properties">
      <i class="fa-solid fa-house"></i> Properties
    </button>
    <button class="section-tab" data-section="messages">
      <i class="fa-solid fa-envelope"></i> Messages
    </button>
  </nav>

  <main class="admin-main">
    <div id="propertiesSection" class="admin-section">
      <div class="admin-toolbar">
        <h2>Property Submissions</h2>

        <div class="status-tabs" id="statusTabs">
          <button class="status-tab active" data-status="pending">Pending</button>
          <button class="status-tab" data-status="approved">Approved</button>
          <button class="status-tab" data-status="rejected">Rejected</button>
          <button class="status-tab" data-status="all">All</button>
        </div>
      </div>

      <div id="adminPropertiesList" class="admin-properties-list">
        <p>Loading properties...</p>
      </div>
    </div>

    <div id="messagesSection" class="admin-section hidden">
      <div class="admin-toolbar">
        <h2>Contact Messages</h2>
      </div>

      <div id="adminMessagesList" class="admin-properties-list">
        <p>Loading messages...</p>
      </div>
    </div>
  </main>

    <!-- IMAGE LIGHTBOX - -->
<div id="imageLightbox" class="image-lightbox hidden">
  <span class="image-lightbox-close" id="imageLightboxClose">&times;</span>
  <img id="imageLightboxImg" src="" alt="Property photo" />
</div>

  <script>
    window.CSRF_TOKEN = "<?php echo htmlspecialchars(getCsrfToken()); ?>";
  </script>
  <script src="js/admin.js"></script>
</body>
</html>



