<?php

header("Content-Type: application/json");

require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "You must be logged in."
    ]);
    exit();
}

$userId = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT id, title, location, price, listing_type, status, reference_code, created_at
     FROM properties
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$properties = [];

while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}

echo json_encode([
    "success" => true,
    "properties" => $properties
]);

$stmt->close();
$conn->close();

?>
