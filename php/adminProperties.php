<?php

header("Content-Type: application/json");

require_once "config.php";


// MUST BE LOGGED IN AS ADMIN


if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    echo json_encode([
        "success" => false,
        "message" => "Access denied."
    ]);
    exit();
}


$statusFilter = $_GET["status"] ?? "all";

$sql = "SELECT
            p.id, p.title, p.location, p.property_type, p.listing_type,
            p.price, p.area, p.reference_code, p.description, p.status,
            p.images, p.created_at,
            u.fullname AS owner_name, u.email AS owner_email
        FROM properties p
        JOIN users u ON u.id = p.user_id";

if ($statusFilter !== "all") {
    $sql .= " WHERE p.status = ?";
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);

if ($statusFilter !== "all") {
    $stmt->bind_param("s", $statusFilter);
}

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