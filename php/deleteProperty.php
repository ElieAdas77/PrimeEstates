<?php

header("Content-Type: application/json");

require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "You must be logged in."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit();
}

requireValidCsrfToken();

$propertyId = $_POST["propertyId"] ?? "";

if (!is_numeric($propertyId)) {
    echo json_encode(["success" => false, "message" => "Invalid property."]);
    exit();
}

$propertyId = (int) $propertyId;
$userId = $_SESSION["user_id"];

// Ownership check + grab images so we can clean up the uploaded files too
$check = $conn->prepare("SELECT images FROM properties WHERE id = ? AND user_id = ?");
$check->bind_param("ii", $propertyId, $userId);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Property not found, or you don't have permission to delete it."]);
    $check->close();
    $conn->close();
    exit();
}

$property = $result->fetch_assoc();
$check->close();

$stmt = $conn->prepare("DELETE FROM properties WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $propertyId, $userId);

if ($stmt->execute()) {

    if (!empty($property["images"])) {
        $uploadDir = "../uploads/properties/";
        foreach (explode(",", $property["images"]) as $file) {
            $path = $uploadDir . trim($file);
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    echo json_encode(["success" => true, "message" => "Property deleted."]);
} else {
    echo json_encode(["success" => false, "message" => "Could not delete property."]);
}

$stmt->close();
$conn->close();