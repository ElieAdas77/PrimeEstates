<?php

require_once "config.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "You must be logged in to save favorites."]);
    exit;
}

requireValidCsrfToken();

$userId = $_SESSION["user_id"];
$propertyId = isset($_POST["propertyId"]) ? (int) $_POST["propertyId"] : 0;

if ($propertyId <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid property."]);
    exit;
}

// Check if this property is already favorited by this user
$stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND property_id = ?");
$stmt->bind_param("ii", $userId, $propertyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Already favorited -> remove it
    $row = $result->fetch_assoc();
    $stmt->close();

    $deleteStmt = $conn->prepare("DELETE FROM favorites WHERE id = ?");
    $deleteStmt->bind_param("i", $row["id"]);
    $deleteStmt->execute();
    $deleteStmt->close();

    echo json_encode(["success" => true, "favorited" => false]);
} else {
    // Not favorited yet 
    $stmt->close();

    $insertStmt = $conn->prepare("INSERT INTO favorites (user_id, property_id) VALUES (?, ?)");
    $insertStmt->bind_param("ii", $userId, $propertyId);

    if ($insertStmt->execute()) {
        echo json_encode(["success" => true, "favorited" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Could not save favorite."]);
    }

    $insertStmt->close();
}

$conn->close();