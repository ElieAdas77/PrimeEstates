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


// RATE LIMIT: max 3 account creations per hour per IP


$ip = getClientIp();

if (isRateLimited($conn, "register", $ip, 3, 60)) {
    echo json_encode([
        "success" => false,
        "message" => "Too many accounts created from this network. Please try again later."
    ]);
    exit();
}

$fullname = trim($_POST["fullname"] ?? "");
$email = trim($_POST["email"] ?? "");
$userPassword = $_POST["password"] ?? "";

if ($fullname === "" || $email === "" || $userPassword === "") {
    echo json_encode([
        "success" => false,
        "message" => "Please fill in all fields"
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

if (strlen($userPassword) < 8) {
    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 8 characters."
    ]);
    exit();
}

/* Check if email already exists */
$check = $conn->prepare(
    "SELECT id FROM users WHERE email = ?"
);

$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "This email is already registered"
    ]);
    $check->close();
    $conn->close();
    exit();
}

$check->close();

/* Encrypt password */
$hashedPassword = password_hash(
    $userPassword,
    PASSWORD_DEFAULT
);

/* Insert new user */
$stmt = $conn->prepare(
    "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)"
);

$stmt->bind_param(
    "sss",
    $fullname,
    $email,
    $hashedPassword
);

if ($stmt->execute()) {

    $newUserId = $stmt->insert_id;

    recordAttempt($conn, "register", $ip);

    

    session_regenerate_id(true);

    $_SESSION["user_id"] = $newUserId;
    $_SESSION["fullname"] = $fullname;
    $_SESSION["email"] = $email;
    $_SESSION["role"] = "user";

    echo json_encode([
        "success" => true,
        "message" => "Account created successfully",
        "fullname" => $fullname,
        "email" => $email,
        "role" => "user"
    ]);

} else {

    recordAttempt($conn, "register", $ip);

    echo json_encode([
        "success" => false,
        "message" => "Error creating account"
    ]);
}

$stmt->close();
$conn->close();

?>