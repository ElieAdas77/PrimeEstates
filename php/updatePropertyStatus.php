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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);
    exit();
}

requireValidCsrfToken();

$propertyId = $_POST["propertyId"] ?? "";
$newStatus = $_POST["status"] ?? "";

$allowedStatuses = ["approved", "rejected", "pending"];

if (!is_numeric($propertyId) || !in_array($newStatus, $allowedStatuses)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid data."
    ]);
    exit();
}

$propertyId = (int) $propertyId;

$stmt = $conn->prepare("UPDATE properties SET status = ? WHERE id = ?");
$stmt->bind_param("si", $newStatus, $propertyId);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Property status updated.",
        "propertyId" => $propertyId,
        "status" => $newStatus
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Could not update property status."
    ]);
}

$stmt->close();
$conn->close();

?>