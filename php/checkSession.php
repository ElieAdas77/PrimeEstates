<?php

header("Content-Type: application/json");

require_once "config.php";

if (isset($_SESSION["user_id"])) {

    echo json_encode([
        "loggedIn" => true,
        "fullname" => $_SESSION["fullname"],
        "email" => $_SESSION["email"],
        "role" => $_SESSION["role"]
    ]);

} else {

    echo json_encode([
        "loggedIn" => false
    ]);
}

?>
