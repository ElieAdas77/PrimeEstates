<?php

header("Content-Type: application/json");

require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "You must be logged in."]);
    exit();
}

$propertyId = $_GET["id"] ?? "";

if (!is_numeric($propertyId)) {
    echo json_encode(["success" => false, "message" => "Invalid property."]);
    exit();
}

$propertyId = (int) $propertyId;
$userId = $_SESSION["user_id"];

// Ownership check: only the user who submitted it can fetch it for editing
$stmt = $conn->prepare(
    "SELECT id, title, price, description, images
     FROM properties
     WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $propertyId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Property not found."]);
    $stmt->close();
    $conn->close();
    exit();
}

$property = $result->fetch_assoc();
$stmt->close();
$conn->close();

echo json_encode(["success" => true, "property" => $property]);