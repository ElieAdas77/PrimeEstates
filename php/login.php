<?php

header("Content-Type: application/json");

require_once "config.php";
require_once "rateLimiter.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);
    exit();
}

requireValidCsrfToken();


// RATE LIMIT: max 5 failed attempts per 15 minutes per IP


$ip = getClientIp();

if (isRateLimited($conn, "login", $ip, 5, 15)) {
    echo json_encode([
        "success" => false,
        "message" => "Too many failed login attempts. Please try again in 15 minutes."
    ]);
    exit();
}



$email = trim($_POST["email"] ?? "");
$userPassword = $_POST["password"] ?? "";

if ($email === "" || $userPassword === "") {
    echo json_encode([
        "success" => false,
        "message" => "Please enter your email and password."
    ]);
    exit();
}



$stmt = $conn->prepare(
    "SELECT id, fullname, email, password, role 
     FROM users 
     WHERE email = ?"
);

$stmt->bind_param("s", $email);

$stmt->execute();

$result = $stmt->get_result();



if ($result->num_rows === 0) {

    recordAttempt($conn, "login", $ip);

    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password."
    ]);

    $stmt->close();
    $conn->close();
    exit();
}



$user = $result->fetch_assoc();



if (!password_verify($userPassword, $user["password"])) {

    recordAttempt($conn, "login", $ip);

    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password."
    ]);

    $stmt->close();
    $conn->close();
    exit();
}




clearAttempts($conn, "login", $ip);

// Regenerate the session id on login to prevent session fixation
session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];
$_SESSION["fullname"] = $user["fullname"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role"];

echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "fullname" => $user["fullname"],
    "email" => $user["email"],
    "role" => $user["role"]
]);

$stmt->close();
$conn->close();

?>