<?php

header("Content-Type: application/json");

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit();
}

requireValidCsrfToken();

$token = $_POST["token"] ?? "";
$newPassword = $_POST["newPassword"] ?? "";

if ($token === "" || $newPassword === "") {
    echo json_encode(["success" => false, "message" => "Missing required fields."]);
    exit();
}

if (strlen($newPassword) < 8) {
    echo json_encode(["success" => false, "message" => "Password must be at least 8 characters."]);
    exit();
}

$stmt = $conn->prepare(
    "SELECT id, user_id FROM password_resets
     WHERE token = ? AND used = 0 AND expires_at > NOW()"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "This reset link is invalid or has expired. Please request a new one."
    ]);
    $stmt->close();
    $conn->close();
    exit();
}

$resetRow = $result->fetch_assoc();
$stmt->close();

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$update->bind_param("si", $hashedPassword, $resetRow["user_id"]);
$update->execute();
$update->close();

// Mark this token used so it can't be replayed, and invalidate
// any OTHER outstanding reset tokens for this user too
$markUsed = $conn->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ?");
$markUsed->bind_param("i", $resetRow["user_id"]);
$markUsed->execute();
$markUsed->close();

$conn->close();

echo json_encode([
    "success" => true,
    "message" => "Your password has been reset. Redirecting you to sign in..."
]);
