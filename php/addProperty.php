<?php

header("Content-Type: application/json");

require_once "config.php";
require_once "imageUploadHelper.php";



if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "You must be logged in to add a property."
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

$userId = $_SESSION["user_id"];



$title       = trim($_POST["title"] ?? "");
$location    = trim($_POST["location"] ?? "");
$propertyType = trim($_POST["propertyType"] ?? "");
$listingType = trim($_POST["listingType"] ?? "sale");
$price       = $_POST["price"] ?? "";
$area        = $_POST["area"] ?? "";
$description = trim($_POST["description"] ?? "");

if (
    $title === "" || $location === "" || $propertyType === "" ||
    $price === "" || $area === "" || $description === ""
) {
    echo json_encode([
        "success" => false,
        "message" => "Please fill in all required fields."
    ]);
    exit();
}


$bedrooms   = $_POST["bedrooms"] !== "" ? (int) $_POST["bedrooms"] : null;
$bathrooms  = $_POST["bathrooms"] !== "" ? (int) $_POST["bathrooms"] : null;
$floor      = $_POST["floor"] !== "" ? (int) $_POST["floor"] : null;
$parking    = $_POST["parking"] !== "" ? (int) $_POST["parking"] : null;
$terrace    = $_POST["terrace"] !== "" ? (float) $_POST["terrace"] : null;
$garden     = $_POST["garden"] !== "" ? (float) $_POST["garden"] : null;
$monthlyFee = $_POST["monthlyFee"] !== "" ? (float) $_POST["monthlyFee"] : null;
$yearBuilt  = $_POST["yearBuilt"] !== "" ? (int) $_POST["yearBuilt"] : null;

$furnished  = $_POST["furnished"] ?? null;
$condition  = $_POST["condition"] ?? null;
$ownership  = $_POST["ownership"] ?? null;

$paymentType = isset($_POST["paymentType"]) ? implode(",", (array) $_POST["paymentType"]) : null;
$amenities   = isset($_POST["amenities"]) ? implode(",", (array) $_POST["amenities"]) : null;

$referenceCode = trim($_POST["reference"] ?? "");
$videoLink     = trim($_POST["videoLink"] ?? "");


$debugInfo = [
    "filesKeyExists" => isset($_FILES["images"]),
    "nameIsArray" => isset($_FILES["images"]["name"]) ? is_array($_FILES["images"]["name"]) : "n/a",
    "rawNameField" => $_FILES["images"]["name"] ?? "not set",
];

$uploadedFileNames = [];
$skippedImages = [];

if (!empty($_FILES["images"]["name"][0])) {

    $uploadDir = __DIR__ . "/../uploads/properties/";

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
            $uploadedFileNames[] = $safeName;
        } else {
            $skippedImages[] = ($originalName !== "" ? $originalName : "Image " . ($index + 1)) . " " . $reason;
        }
    }
}

$imagesValue = !empty($uploadedFileNames) ? implode(",", $uploadedFileNames) : null;



$stmt = $conn->prepare(
    "INSERT INTO properties (
        user_id, title, location, property_type, listing_type, price, area,
        bedrooms, bathrooms, floor, parking, terrace, garden, monthly_fee, year_built,
        furnished, condition_status, ownership, payment_type, amenities,
        reference_code, description, video_link, images, status
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?, 'pending'
    )"
);

$stmt->bind_param(
    "issssdd" . "iiiidddi" . "sss" . "ss" . "ssss",
    $userId, $title, $location, $propertyType, $listingType, $price, $area,
    $bedrooms, $bathrooms, $floor, $parking, $terrace, $garden, $monthlyFee, $yearBuilt,
    $furnished, $condition, $ownership, $paymentType, $amenities,
    $referenceCode, $description, $videoLink, $imagesValue
);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Error saving property. Please try again."
    ]);
    $stmt->close();
    $conn->close();
    exit();
}

$newPropertyId = $stmt->insert_id;
$stmt->close();

// Auto-generate a reference code if the user didn't provide one
if ($referenceCode === "") {
    $referenceCode = "PE-" . str_pad($newPropertyId, 4, "0", STR_PAD_LEFT);

    $update = $conn->prepare("UPDATE properties SET reference_code = ? WHERE id = ?");
    $update->bind_param("si", $referenceCode, $newPropertyId);
    $update->execute();
    $update->close();
}

$message = "Your property has been submitted for review.";
if (!empty($skippedImages)) {
    $message .= " Note: " . count($skippedImages) . " image(s) were skipped - " . implode("; ", $skippedImages) . ".";
}

echo json_encode([
    "success" => true,
    "message" => $message,
    "propertyId" => $newPropertyId,
    "reference" => $referenceCode,
    "title" => $title,
    "skippedImages" => $skippedImages,
    "DEBUG" => $debugInfo
]);

$conn->close();

?>