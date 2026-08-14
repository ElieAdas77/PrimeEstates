<?php

header("Content-Type: application/json");

require_once "config.php";

requireValidCsrfToken();

// Clear all session data
$_SESSION = [];

// Remove the session cookie itself
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session on the server
session_destroy();

echo json_encode([
    "success" => true,
    "message" => "Logged out"
]);

?>