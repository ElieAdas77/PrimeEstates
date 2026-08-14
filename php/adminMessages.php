<?php

header("Content-Type: application/json");

require_once "config.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    echo json_encode([
        "success" => false,
        "message" => "Access denied."
    ]);
    exit();
}

$result = $conn->query(
    "SELECT id, name, email, subject, message, is_read, created_at
     FROM messages
     ORDER BY created_at DESC"
);

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode([
    "success" => true,
    "messages" => $messages
]);

$conn->close();

?>
