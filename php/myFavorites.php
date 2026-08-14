<?php

require_once "config.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "You must be logged in to view favorites."]);
    exit;
}

$userId = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT p.id, p.title, p.location, p.price, p.listing_type, p.reference_code, p.status
     FROM favorites f
     JOIN properties p ON p.id = f.property_id
     WHERE f.user_id = ?
     ORDER BY f.created_at DESC"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}
$stmt->close();

echo json_encode(["success" => true, "favorites" => $favorites]);

$conn->close();
