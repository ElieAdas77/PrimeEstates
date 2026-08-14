<?php

header("Content-Type: application/json");

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);
    exit();
}

requireValidCsrfToken();

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

if ($name === "" || $email === "" || $subject === "" || $message === "") {
    echo json_encode([
        "success" => false,
        "message" => "Please fill in all fields."
    ]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid email address."
    ]);
    exit();
}

// If they're logged in, link the message to their account
$userId = $_SESSION["user_id"] ?? null;

$stmt = $conn->prepare(
    "INSERT INTO messages (user_id, name, email, subject, message)
     VALUES (?, ?, ?, ?, ?)"
);

$stmt->bind_param("issss", $userId, $name, $email, $subject, $message);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Your message has been sent. We will contact you shortly."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Something went wrong. Please try again."
    ]);
}

$stmt->close();
$conn->close();

?>