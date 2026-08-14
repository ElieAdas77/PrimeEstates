<?php

header("Content-Type: application/json");

require_once "config.php";
require_once "imageUploadHelper.php";

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


$check = $conn->prepare("SELECT images FROM properties WHERE id = ? AND user_id = ?");
$check->bind_param("ii", $propertyId, $userId);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Property not found, or you don't have permission to edit it."]);
    $check->close();
    $conn->close();
    exit();
}

$existing = $result->fetch_assoc();
$check->close();

$title = trim($_POST["title"] ?? "");
$price = $_POST["price"] ?? "";
$description = trim($_POST["description"] ?? "");

if ($title === "" || $price === "" || $description === "") {
    echo json_encode(["success" => false, "message" => "Please fill in all fields."]);
    exit();
}

if (!is_numeric($price) || $price <= 0) {
    echo json_encode(["success" => false, "message" => "Please enter a valid price."]);
    exit();
}

// Only touch images if new ones were actually uploaded -> otherwise
// keep the existing set untouched
$imagesValue = $existing["images"];
$skippedImages = [];

if (!empty($_FILES["images"]["name"][0])) {
    $uploadDir = "../uploads/properties/";

    $newFilenames = [];
    foreach ($_FILES["images"]["name"] as $index => $originalName) {
        $tmpPath = $_FILES["images"]["tmp_name"][$index];
        $errorCode = $_FILES["images"]["error"][$index];

        $reason = null;
        $safeName = saveValidatedImage(
            $tmpPath,
            $errorCode,
            $uploadDir,
            "prop_" . $userId . "_" . time() . "_" . $index,
            $reason
        );

        if ($safeName !== null) {
            $newFilenames[] = $safeName;
        } else {
            $skippedImages[] = ($originalName !== "" ? $originalName : "Image " . ($index + 1)) . " " . $reason;
        }
    }

    if (!empty($newFilenames)) {
        // Delete the old image files from disk now that they're being replaced
        if (!empty($existing["images"])) {
            foreach (explode(",", $existing["images"]) as $oldFile) {
                $oldPath = $uploadDir . trim($oldFile);
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }
        }
        $imagesValue = implode(",", $newFilenames);
    }
}

// Any edit sends the listing back to pending 

$stmt = $conn->prepare(
    "UPDATE properties
     SET title = ?, price = ?, description = ?, images = ?, status = 'pending'
     WHERE id = ? AND user_id = ?"
);
$stmt->bind_param("sdssii", $title, $price, $description, $imagesValue, $propertyId, $userId);

if ($stmt->execute()) {
    $message = "Property updated. It's back in review and will need admin approval before it's public again.";
    if (!empty($skippedImages)) {
        $message .= " Note: " . count($skippedImages) . " image(s) were skipped - " . implode("; ", $skippedImages) . ".";
    }

    echo json_encode([
        "success" => true,
        "message" => $message,
        "skippedImages" => $skippedImages
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Could not update property."]);
}

$stmt->close();
$conn->close();