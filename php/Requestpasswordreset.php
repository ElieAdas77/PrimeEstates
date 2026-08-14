<?php

header("Content-Type: application/json");

require_once "config.php";
require_once "rateLimiter.php";
require_once "simpleMailer.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit();
}

requireValidCsrfToken();

$ip = getClientIp();

// Reuse the rate limiter: max 3 reset requests per hour per IP
if (isRateLimited($conn, "password_reset", $ip, 3, 60)) {
    echo json_encode([
        "success" => false,
        "message" => "Too many reset requests. Please try again later."
    ]);
    exit();
}

$email = trim($_POST["email"] ?? "");

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Please enter a valid email address."]);
    exit();
}

recordAttempt($conn, "password_reset", $ip);


$genericResponse = [
    "success" => true,
    "message" => "If an account exists for that email, a reset link has been sent."
];

$stmt = $conn->prepare("SELECT id, fullname FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    echo json_encode($genericResponse);
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();


$token = bin2hex(random_bytes(32));

$insert = $conn->prepare(
    "INSERT INTO password_resets (user_id, token, expires_at)
     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))"
);
$insert->bind_param("is", $user["id"], $token);
$insert->execute();
$insert->close();


$protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
$host = $_SERVER["HTTP_HOST"] ?? "localhost";
$resetLink = "$protocol://$host/PrimeEstates/resetPassword.php?token=" . urlencode($token);

$emailBody = "
    <p>Hi " . htmlspecialchars($user['fullname']) . ",</p>
    <p>We received a request to reset your PrimeEstates password. Click the link below to choose a new one. This link expires in 1 hour.</p>
    <p><a href=\"$resetLink\">$resetLink</a></p>
    <p>If you didn't request this, you can safely ignore this email.</p>
";

$mailResult = sendEmail($email, "Reset your PrimeEstates password", $emailBody);


if ($mailResult !== true) {
    error_log("Password reset email failed for $email: $mailResult");
}

$conn->close();

echo json_encode($genericResponse);