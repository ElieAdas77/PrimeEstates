<?php



function getClientIp() {
    return $_SERVER["REMOTE_ADDR"] ?? "unknown";
}


function isRateLimited($conn, $action, $ip, $maxAttempts, $windowMinutes) {
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS attempt_count
         FROM rate_limits
         WHERE action = ? AND ip_address = ?
           AND attempted_at > (NOW() - INTERVAL ? MINUTE)"
    );
    $stmt->bind_param("ssi", $action, $ip, $windowMinutes);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return (int) $row["attempt_count"] >= $maxAttempts;
}

function recordAttempt($conn, $action, $ip) {
    $stmt = $conn->prepare(
        "INSERT INTO rate_limits (action, ip_address) VALUES (?, ?)"
    );
    $stmt->bind_param("ss", $action, $ip);
    $stmt->execute();
    $stmt->close();
}


function clearAttempts($conn, $action, $ip) {
    $stmt = $conn->prepare(
        "DELETE FROM rate_limits WHERE action = ? AND ip_address = ?"
    );
    $stmt->bind_param("ss", $action, $ip);
    $stmt->execute();
    $stmt->close();
}
