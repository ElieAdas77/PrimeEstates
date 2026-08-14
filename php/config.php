<?php


if (file_exists(__DIR__ . "/mailconfig.local.php")) {
    require_once __DIR__ . "/mailconfig.local.php";
}



define("ENVIRONMENT", "development"); // change to "production" when deploying

if (ENVIRONMENT === "production") {
    error_reporting(0);
    ini_set("display_errors", "0");
} else {
    error_reporting(E_ALL);
    ini_set("display_errors", "1");
}

// Start (or resume) the PHP session on every page/endpoint that includes this file
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "primeestates";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {

    error_log("Database connection failed: " . $conn->connect_error);

    if (ENVIRONMENT === "production") {
        die("Something went wrong. Please try again later.");
    } else {
        die("Connection failed: " . $conn->connect_error);
    }
}



function getCsrfToken() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function verifyCsrfToken($submittedToken) {
    if (empty($_SESSION["csrf_token"]) || empty($submittedToken)) {
        return false;
    }
    return hash_equals($_SESSION["csrf_token"], (string) $submittedToken);
}


function requireValidCsrfToken() {
    $token = $_POST["csrfToken"] ?? "";
    if (!verifyCsrfToken($token)) {
        header("Content-Type: application/json");
        echo json_encode([
            "success" => false,
            "message" => "Your session security check failed. Please refresh the page and try again."
        ]);
        exit();
    }
}

?>